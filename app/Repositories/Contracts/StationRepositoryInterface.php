<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\StationDto;
use App\Models\Stop;
use Illuminate\Support\Collection;

interface StationRepositoryInterface
{
    public function findById(int $id): ?Stop;

    public function findDtoById(int $id): ?StationDto;

    /**
     * Search stations by keyword autocomplete.
     *
     * @return Collection<int, StationDto>
     */
    public function search(string $query, int $limit = 10): Collection;

    /**
     * Find nearby stations using PostgreSQL GiST earthdistance.
     *
     * @return Collection<int, StationDto>
     */
    public function findNearby(float $latitude, float $longitude, int $radiusMeters = 1000, int $limit = 5): Collection;

    /**
     * Get all active stations for map markers.
     *
     * @return Collection<int, StationDto>
     */
    public function getAllActive(): Collection;
}
