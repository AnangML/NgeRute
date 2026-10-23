<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RouteSegment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'route_id',
        'from_stop_id',
        'to_stop_id',
        'sequence_order',
        'distance_meters',
        'duration_seconds',
        'path_coordinates',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'distance_meters' => 'integer',
            'duration_seconds' => 'integer',
            'path_coordinates' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Route, $this>
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * @return BelongsTo<Stop, $this>
     */
    public function fromStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'from_stop_id');
    }

    /**
     * @return BelongsTo<Stop, $this>
     */
    public function toStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'to_stop_id');
    }
}
