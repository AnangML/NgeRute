<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class JourneyResponseDto
{
    /**
     * @param list<RouteOptionDto> $routes
     * @param array<string, mixed> $provenance
     */
    public function __construct(
        public StationDto $origin,
        public StationDto $destination,
        public array $routes,
        public array $provenance = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'origin' => $this->origin->toArray(),
            'destination' => $this->destination->toArray(),
            'routes' => array_map(fn (RouteOptionDto $r) => $r->toArray(), $this->routes),
            'provenance' => $this->provenance,
        ];
    }
}
