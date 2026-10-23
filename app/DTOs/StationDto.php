<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class StationDto
{
    public function __construct(
        public int $id,
        public ?string $code,
        public string $name,
        public float $latitude,
        public float $longitude,
        public string $locationType,
        public ?string $operatorCode,
        public ?string $operatorName,
        public ?string $mode,
        public ?string $colorHex,
        public ?float $distanceMeters = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_type' => $this->locationType,
            'operator_code' => $this->operatorCode,
            'operator_name' => $this->operatorName,
            'mode' => $this->mode,
            'color_hex' => $this->colorHex,
            'distance_meters' => $this->distanceMeters !== null ? round($this->distanceMeters, 1) : null,
        ];
    }
}
