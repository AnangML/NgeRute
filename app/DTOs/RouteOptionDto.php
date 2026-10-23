<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\RouteCriteria;

final readonly class RouteOptionDto
{
    /**
     * @param list<SegmentDto> $segments
     * @param list<string> $badges
     * @param list<array{0: float, 1: float}> $fullPolyline
     */
    public function __construct(
        public string $id, // 'route-1', 'route-2'
        public RouteCriteria $primaryCriteria,
        public array $badges,
        public int $totalDurationSeconds,
        public int $totalFareIdr,
        public int $totalDistanceMeters,
        public int $walkingDistanceMeters,
        public int $walkingDurationSeconds,
        public int $transferCount,
        public array $segments,
        public array $fullPolyline = [],
    ) {}

    public function totalDurationMinutes(): int
    {
        return (int) max(1, ceil($this->totalDurationSeconds / 60));
    }

    public function walkingDurationMinutes(): int
    {
        return (int) ceil($this->walkingDurationSeconds / 60);
    }

    public function formattedFare(): string
    {
        if ($this->totalFareIdr <= 0) {
            return 'Gratis / Belum tersedia';
        }

        return 'Rp' . number_format($this->totalFareIdr, 0, ',', '.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'primary_criteria' => $this->primaryCriteria->value,
            'criteria_label' => $this->primaryCriteria->label(),
            'badges' => $this->badges,
            'total_duration_seconds' => $this->totalDurationSeconds,
            'total_duration_minutes' => $this->totalDurationMinutes(),
            'total_fare_idr' => $this->totalFareIdr,
            'formatted_fare' => $this->formattedFare(),
            'total_distance_meters' => $this->totalDistanceMeters,
            'walking_distance_meters' => $this->walkingDistanceMeters,
            'walking_duration_seconds' => $this->walkingDurationSeconds,
            'walking_duration_minutes' => $this->walkingDurationMinutes(),
            'transfer_count' => $this->transferCount,
            'segments' => array_map(fn (SegmentDto $s) => $s->toArray(), $this->segments),
            'full_polyline' => $this->fullPolyline,
        ];
    }
}
