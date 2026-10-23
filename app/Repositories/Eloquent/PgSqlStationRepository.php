<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\StationDto;
use App\Models\Stop;
use App\Repositories\Contracts\StationRepositoryInterface;
use Illuminate\Support\Collection;

final class PgSqlStationRepository implements StationRepositoryInterface
{
    public function findById(int $id): ?Stop
    {
        return Stop::with('operator')->find($id);
    }

    public function findDtoById(int $id): ?StationDto
    {
        $stop = $this->findById($id);

        return $stop ? $this->mapToDto($stop) : null;
    }

    public function search(string $query, int $limit = 10): Collection
    {
        $term = '%' . trim(strtolower($query)) . '%';

        return Stop::with('operator')
            ->where('is_active', true)
            ->where(function ($q) use ($term): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$term]);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Stop $s) => $this->mapToDto($s));
    }

    public function findNearby(float $latitude, float $longitude, int $radiusMeters = 1000, int $limit = 5): Collection
    {
        return Stop::with('operator')
            ->where('is_active', true)
            ->nearby($latitude, $longitude, $radiusMeters)
            ->limit($limit)
            ->get()
            ->map(fn (Stop $s) => $this->mapToDto($s, (float) ($s->distance_meters ?? 0)));
    }

    public function getAllActive(): Collection
    {
        return Stop::with('operator')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Stop $s) => $this->mapToDto($s));
    }

    private function mapToDto(Stop $stop, ?float $distanceMeters = null): StationDto
    {
        return new StationDto(
            id: (int) $stop->id,
            code: $stop->code,
            name: $stop->name,
            latitude: (float) $stop->latitude,
            longitude: (float) $stop->longitude,
            locationType: $stop->location_type ?? 'station',
            operatorCode: $stop->operator?->code,
            operatorName: $stop->operator?->name,
            mode: $stop->operator?->mode?->value,
            colorHex: $stop->operator?->color_hex,
            distanceMeters: $distanceMeters,
        );
    }
}
