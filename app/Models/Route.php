<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Route extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'operator_id',
        'code',
        'name',
        'mode',
        'color_hex',
        'is_active',
        'source_updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => TransportMode::class,
            'is_active' => 'boolean',
            'source_updated_at' => 'date',
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
    public function segments(): HasMany
    {
        return $this->hasMany(RouteSegment::class)->orderBy('sequence_order');
    }
}
