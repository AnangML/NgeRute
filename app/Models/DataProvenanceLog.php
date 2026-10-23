<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class DataProvenanceLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'dataset_name',
        'operator_code',
        'records_count',
        'status',
        'verified_by',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'records_count' => 'integer',
        ];
    }
}
