<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransportMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Operator extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'mode',
        'color_hex',
        'official_url',
        'data_source_url',
        'license_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => TransportMode::class,
        ];
    }

    /**
     * @return HasMany<Stop, $this>
     */
    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class);
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * @return HasMany<FareRule, $this>
     */
    public function fareRules(): HasMany
    {
        return $this->hasMany(FareRule::class);
    }
}
