<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Transfer extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_stop_id',
        'to_stop_id',
        'walking_distance_meters',
        'walking_duration_seconds',
        'transfer_type',
        'source_notes',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'walking_distance_meters' => 'integer',
            'walking_duration_seconds' => 'integer',
            'transfer_type' => TransferType::class,
            'verified_at' => 'date',
        ];
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
