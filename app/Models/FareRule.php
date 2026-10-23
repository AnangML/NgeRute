<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FareRule extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'operator_id',
        'fare_scheme',
        'base_fare',
        'base_distance_km',
        'fare_per_next_km',
        'max_fare',
        'matrix_payload',
        'valid_from',
        'source_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_fare' => 'float',
            'base_distance_km' => 'float',
            'fare_per_next_km' => 'float',
            'max_fare' => 'float',
            'matrix_payload' => 'array',
            'valid_from' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Operator, $this>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
