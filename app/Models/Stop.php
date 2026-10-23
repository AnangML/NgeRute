<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Stop extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'operator_id',
        'code',
        'name',
        'latitude',
        'longitude',
        'location_type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Operator, $this>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * @return HasMany<RouteSegment, $this>
     */
    public function departingSegments(): HasMany
    {
        return $this->hasMany(RouteSegment::class, 'from_stop_id');
    }

    /**
     * @return HasMany<RouteSegment, $this>
     */
    public function arrivingSegments(): HasMany
    {
        return $this->hasMany(RouteSegment::class, 'to_stop_id');
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_stop_id');
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_stop_id');
    }

    /**
     * Scope for PostgreSQL spatial radius search using earthdistance
     *
     * @param Builder<Stop> $query
     * @return Builder<Stop>
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, int $radiusMeters = 1000): Builder
    {
        return $query
            ->select('*')
            ->selectRaw('earth_distance(ll_to_earth(latitude, longitude), ll_to_earth(?, ?)) as distance_meters', [
                $latitude,
                $longitude,
            ])
            ->whereRaw('earth_distance(ll_to_earth(latitude, longitude), ll_to_earth(?, ?)) <= ?', [
                $latitude,
                $longitude,
                $radiusMeters,
            ])
            ->orderBy('distance_meters', 'asc');
    }
}
