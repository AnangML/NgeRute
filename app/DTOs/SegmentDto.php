<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class SegmentDto
{
    /**
     * @param list<array{id: int, name: string, code: ?string, latitude: float, longitude: float}> $intermediateStops
     * @param list<array{0: float, 1: float}> $polyline
     */
    public function __construct(
        public string $type, // 'transit' | 'transfer'
        public StationDto $fromStop,
        public StationDto $toStop,
        public int $distanceMeters,
        public int $durationSeconds,
        public ?string $mode = null,
        public ?string $operatorName = null,
        public ?string $operatorCode = null,
        public ?string $routeCode = null,
        public ?string $routeName = null,
        public ?string $headsign = null,
        public ?string $colorHex = null,
        public ?string $transferType = null,
        public ?string $transferNotes = null,
        public int $fareIdr = 0,
        public int $stopsCount = 0,
        public array $intermediateStops = [],
        public array $polyline = [],
    ) {}

    public function durationMinutes(): int
    {
        return (int) max(1, ceil($this->durationSeconds / 60));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'from_stop' => $this->fromStop->toArray(),
            'to_stop' => $this->toStop->toArray(),
            'distance_meters' => $this->distanceMeters,
            'duration_seconds' => $this->durationSeconds,
            'duration_minutes' => $this->durationMinutes(),
            'mode' => $this->mode,
            'operator_name' => $this->operatorName,
            'operator_code' => $this->operatorCode,
            'route_code' => $this->routeCode,
            'route_name' => $this->routeName,
            'headsign' => $this->headsign,
            'color_hex' => $this->colorHex,
            'transfer_type' => $this->transferType,
            'transfer_notes' => $this->transferNotes,
            'fare_idr' => $this->fareIdr,
            'stops_count' => $this->stopsCount,
            'intermediate_stops' => $this->intermediateStops,
            'polyline' => $this->polyline,
        ];
    }
}
