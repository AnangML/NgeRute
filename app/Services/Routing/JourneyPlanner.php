<?php

declare(strict_types=1);

namespace App\Services\Routing;

use App\DTOs\JourneyRequestDto;
use App\DTOs\JourneyResponseDto;
use App\DTOs\RouteOptionDto;
use App\DTOs\SegmentDto;
use App\DTOs\StationDto;
use App\DTOs\TransitEdgeDto;
use App\Enums\RouteCriteria;
use App\Exceptions\RouteNotFoundException;
use App\Exceptions\StationNotFoundException;
use App\Models\DataProvenanceLog;
use App\Repositories\Contracts\StationRepositoryInterface;
use App\Services\Fares\FareCalculator;

final readonly class JourneyPlanner
{
    public function __construct(
        private StationRepositoryInterface $stationRepository,
        private MultimodalGraphBuilder $graphBuilder,
        private DijkstraRouter $router,
        private FareCalculator $fareCalculator,
    ) {}

    /**
     * Plan and compare multimodal journeys between origin and destination.
     *
     * @throws StationNotFoundException
     * @throws RouteNotFoundException
     */
    public function plan(JourneyRequestDto $request): JourneyResponseDto
    {
        $originStop = $this->stationRepository->findDtoById($request->originStopId);
        if ($originStop === null) {
            throw StationNotFoundException::forId($request->originStopId);
        }

        $destStop = $this->stationRepository->findDtoById($request->destinationStopId);
        if ($destStop === null) {
            throw StationNotFoundException::forId($request->destinationStopId);
        }

        $graph = $this->graphBuilder->build();

        // 1. Calculate paths for all 4 criteria
        $criteriaList = [
            RouteCriteria::FASTEST,
            RouteCriteria::CHEAPEST,
            RouteCriteria::LEAST_WALKING,
            RouteCriteria::FEWEST_TRANSFERS,
        ];

        /** @var array<string, array{criteria: RouteCriteria, edges: list<TransitEdgeDto>}> $uniquePaths */
        $uniquePaths = [];

        foreach ($criteriaList as $criteria) {
            $edges = $this->router->findPath($graph, $originStop->id, $destStop->id, $criteria);
            if (empty($edges)) {
                continue;
            }

            // Fingerprint path to deduplicate identical routes
            $signature = implode('->', array_map(
                fn (TransitEdgeDto $e) => "{$e->fromStopId}:{$e->toStopId}:{$e->edgeType}:{$e->routeId}",
                $edges
            ));

            if (!isset($uniquePaths[$signature])) {
                $uniquePaths[$signature] = [
                    'primary_criteria' => $criteria,
                    'edges' => $edges,
                    'badges' => [$criteria->badgeText()],
                ];
            } else {
                // If identical path satisfies another criteria, attach the badge
                if (!in_array($criteria->badgeText(), $uniquePaths[$signature]['badges'], true)) {
                    $uniquePaths[$signature]['badges'][] = $criteria->badgeText();
                }
            }
        }

        if (empty($uniquePaths)) {
            throw RouteNotFoundException::between($originStop->name, $destStop->name);
        }

        // 2. Build detailed route options
        $routeOptions = [];
        $index = 1;

        foreach ($uniquePaths as $pathData) {
            $routeOption = $this->buildRouteOption(
                id: "route-{$index}",
                primaryCriteria: $pathData['primary_criteria'],
                badges: $pathData['badges'],
                edges: $pathData['edges'],
                origin: $originStop,
                destination: $destStop
            );

            $routeOptions[] = $routeOption;
            $index++;
        }

        // 3. Fetch latest data provenance info
        $latestLog = DataProvenanceLog::latest()->first();
        $provenance = [
            'last_verified_at' => $latestLog?->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'dataset_name' => $latestLog?->dataset_name ?? 'Dataset Terverifikasi Jabodetabek',
            'status' => $latestLog?->status ?? 'verified',
            'verified_by' => $latestLog?->verified_by ?? 'NgeRute Engineering',
        ];

        return new JourneyResponseDto(
            origin: $originStop,
            destination: $destStop,
            routes: $routeOptions,
            provenance: $provenance
        );
    }

    /**
     * @param list<string> $badges
     * @param list<TransitEdgeDto> $edges
     */
    private function buildRouteOption(
        string $id,
        RouteCriteria $primaryCriteria,
        array $badges,
        array $edges,
        StationDto $origin,
        StationDto $destination
    ): RouteOptionDto {
        $segments = $this->consolidateEdgesIntoSegments($edges);

        $totalDuration = 0;
        $totalDistance = 0;
        $totalFare = 0;
        $walkingDistance = 0;
        $walkingDuration = 0;
        $transferCount = 0;

        foreach ($segments as $seg) {
            $totalDuration += $seg->durationSeconds;
            $totalDistance += $seg->distanceMeters;
            $totalFare += $seg->fareIdr;

            if ($seg->type === 'transfer') {
                $walkingDistance += $seg->distanceMeters;
                $walkingDuration += $seg->durationSeconds;
                $transferCount++;
            }
        }

        // Extract full polyline coordinates for map drawing
        $fullPolyline = [];
        foreach ($segments as $seg) {
            foreach ($seg->polyline as $coord) {
                $fullPolyline[] = $coord;
            }
        }

        return new RouteOptionDto(
            id: $id,
            primaryCriteria: $primaryCriteria,
            badges: $badges,
            totalDurationSeconds: $totalDuration,
            totalFareIdr: $totalFare,
            totalDistanceMeters: $totalDistance,
            walkingDistanceMeters: $walkingDistance,
            walkingDurationSeconds: $walkingDuration,
            transferCount: $transferCount,
            segments: $segments,
            fullPolyline: $fullPolyline
        );
    }

    /**
     * Consolidates consecutive hops on the same route into a single readable transit segment.
     *
     * @param list<TransitEdgeDto> $edges
     * @return list<SegmentDto>
     */
    private function consolidateEdgesIntoSegments(array $edges): array
    {
        $segments = [];
        $edgeCount = count($edges);
        $i = 0;

        while ($i < $edgeCount) {
            $firstEdge = $edges[$i];

            if ($firstEdge->isTransfer()) {
                // Transfer edges are standalone walking legs
                $fromStop = $this->stationRepository->findDtoById($firstEdge->fromStopId);
                $toStop = $this->stationRepository->findDtoById($firstEdge->toStopId);

                if ($fromStop && $toStop) {
                    $polyline = !empty($firstEdge->pathCoordinates)
                        ? $firstEdge->pathCoordinates
                        : [
                            [$fromStop->latitude, $fromStop->longitude],
                            [$toStop->latitude, $toStop->longitude],
                        ];

                    $segments[] = new SegmentDto(
                        type: 'transfer',
                        fromStop: $fromStop,
                        toStop: $toStop,
                        distanceMeters: $firstEdge->distanceMeters,
                        durationSeconds: $firstEdge->durationSeconds,
                        mode: 'walk',
                        colorHex: '#64748B',
                        transferType: $firstEdge->transferType,
                        transferNotes: $firstEdge->transferNotes,
                        fareIdr: 0,
                        stopsCount: 0,
                        intermediateStops: [],
                        polyline: $polyline
                    );
                }
                $i++;
                continue;
            }

            // Transit edge: group consecutive edges with the same routeId
            $currentRouteId = $firstEdge->routeId;
            $segFromId = $firstEdge->fromStopId;
            $segToId = $firstEdge->toStopId;
            $accumulatedDuration = $firstEdge->durationSeconds;
            $accumulatedDistance = $firstEdge->distanceMeters;
            $hopsCount = 1;
            $intermediateStops = [];

            $j = $i + 1;
            while ($j < $edgeCount && $edges[$j]->isTransit() && $edges[$j]->routeId === $currentRouteId) {
                $intermediateStopDto = $this->stationRepository->findDtoById($edges[$j]->fromStopId);
                if ($intermediateStopDto) {
                    $intermediateStops[] = [
                        'id' => $intermediateStopDto->id,
                        'name' => $intermediateStopDto->name,
                        'code' => $intermediateStopDto->code,
                        'latitude' => $intermediateStopDto->latitude,
                        'longitude' => $intermediateStopDto->longitude,
                    ];
                }

                $segToId = $edges[$j]->toStopId;
                $accumulatedDuration += $edges[$j]->durationSeconds;
                $accumulatedDistance += $edges[$j]->distanceMeters;
                $hopsCount++;
                $j++;
            }

            $fromStop = $this->stationRepository->findDtoById($segFromId);
            $toStop = $this->stationRepository->findDtoById($segToId);

            if ($fromStop && $toStop) {
                // Build realistic polyline from edge path_coordinates or fallback to station points
                $polyline = [];
                for ($k = $i; $k < $j; $k++) {
                    $hopEdge = $edges[$k];
                    if (!empty($hopEdge->pathCoordinates)) {
                        foreach ($hopEdge->pathCoordinates as $coord) {
                            $formattedCoord = [(float) $coord[0], (float) $coord[1]];
                            if (empty($polyline) || end($polyline) !== $formattedCoord) {
                                $polyline[] = $formattedCoord;
                            }
                        }
                    } else {
                        $eFrom = $this->stationRepository->findDtoById($hopEdge->fromStopId);
                        $eTo = $this->stationRepository->findDtoById($hopEdge->toStopId);
                        if ($eFrom && $eTo) {
                            $fromPt = [$eFrom->latitude, $eFrom->longitude];
                            $toPt = [$eTo->latitude, $eTo->longitude];
                            if (empty($polyline) || end($polyline) !== $fromPt) {
                                $polyline[] = $fromPt;
                            }
                            $polyline[] = $toPt;
                        }
                    }
                }

                if (count($polyline) < 2) {
                    $polyline = [
                        [$fromStop->latitude, $fromStop->longitude],
                        [$toStop->latitude, $toStop->longitude],
                    ];
                }

                $fare = $this->fareCalculator->calculate(
                    operatorCode: $firstEdge->operatorCode,
                    distanceMeters: $accumulatedDistance,
                    stopsCount: $hopsCount
                );

                $segments[] = new SegmentDto(
                    type: 'transit',
                    fromStop: $fromStop,
                    toStop: $toStop,
                    distanceMeters: $accumulatedDistance,
                    durationSeconds: $accumulatedDuration,
                    mode: $firstEdge->mode,
                    operatorName: $firstEdge->operatorName,
                    operatorCode: $firstEdge->operatorCode,
                    routeCode: $firstEdge->routeCode,
                    routeName: $firstEdge->routeName,
                    headsign: $firstEdge->headsign,
                    colorHex: $firstEdge->colorHex,
                    fareIdr: $fare,
                    stopsCount: $hopsCount,
                    intermediateStops: $intermediateStops,
                    polyline: $polyline
                );
            }

            $i = $j;
        }

        return $segments;
    }
}
