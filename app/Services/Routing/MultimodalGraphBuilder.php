<?php

declare(strict_types=1);

namespace App\Services\Routing;

use App\DTOs\TransitEdgeDto;
use App\Repositories\Contracts\TransitGraphRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final class MultimodalGraphBuilder
{
    private const CACHE_KEY = 'ngerute_multimodal_graph_raw';
    private const CACHE_TTL_SECONDS = 3600;

    /** @var array<int, list<TransitEdgeDto>>|null */
    private static ?array $runtimeGraph = null;

    public function __construct(
        private readonly TransitGraphRepositoryInterface $repository,
    ) {}

    /**
     * Build or retrieve the cached in-memory adjacency list graph.
     *
     * @return array<int, list<TransitEdgeDto>> Keyed by stop_id
     */
    public function build(bool $forceFresh = false): array
    {
        if ($forceFresh) {
            $this->clearCache();
        }

        if (self::$runtimeGraph !== null) {
            return self::$runtimeGraph;
        }

        /** @var array<int, list<array<string, mixed>>> $rawArrayGraph */
        $rawArrayGraph = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            return $this->buildRawArrayGraph();
        });

        // Hydrate DTOs
        $graph = [];
        foreach ($rawArrayGraph as $stopId => $edgeList) {
            $graph[(int) $stopId] = array_map(fn (array $data) => TransitEdgeDto::fromArray($data), $edgeList);
        }

        self::$runtimeGraph = $graph;

        return $graph;
    }

    public function clearCache(): void
    {
        self::$runtimeGraph = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, list<array<string, mixed>>>
     */
    private function buildRawArrayGraph(): array
    {
        $graph = [];

        // Initialize nodes for all active stops
        $stops = $this->repository->getAllStops();
        foreach ($stops as $stop) {
            $graph[(int) $stop->id] = [];
        }

        // 1. Add transit segments (in-vehicle edges)
        $segments = $this->repository->getAllRouteSegments();
        foreach ($segments as $seg) {
            $fromId = (int) $seg->from_stop_id;
            $toId = (int) $seg->to_stop_id;

            $edge = new TransitEdgeDto(
                fromStopId: $fromId,
                toStopId: $toId,
                distanceMeters: (int) $seg->distance_meters,
                durationSeconds: (int) $seg->duration_seconds,
                edgeType: 'transit',
                routeId: (int) $seg->route_id,
                routeCode: $seg->route?->code,
                routeName: $seg->route?->name,
                headsign: $seg->route?->headsign,
                operatorCode: $seg->route?->operator?->code,
                operatorName: $seg->route?->operator?->name,
                mode: $seg->route?->mode?->value,
                colorHex: $seg->route?->color_hex ?? $seg->route?->operator?->color_hex,
                pathCoordinates: is_array($seg->path_coordinates) ? $seg->path_coordinates : null,
            );

            $graph[$fromId][] = $edge->toArray();
        }

        // 2. Add physical walking transfers
        $transfers = $this->repository->getAllTransfers();
        foreach ($transfers as $tr) {
            $fromId = (int) $tr->from_stop_id;
            $toId = (int) $tr->to_stop_id;

            $edge = new TransitEdgeDto(
                fromStopId: $fromId,
                toStopId: $toId,
                distanceMeters: (int) $tr->walking_distance_meters,
                durationSeconds: (int) $tr->walking_duration_seconds,
                edgeType: 'transfer',
                mode: 'walk',
                colorHex: '#64748B',
                transferType: $tr->transfer_type?->value,
                transferNotes: $tr->source_notes,
                pathCoordinates: is_array($tr->path_coordinates) ? $tr->path_coordinates : null,
            );

            $graph[$fromId][] = $edge->toArray();
        }

        return $graph;
    }
}
