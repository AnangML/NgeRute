<?php

declare(strict_types=1);

namespace App\Enums;

enum RouteCriteria: string
{
    case FASTEST = 'fastest';
    case CHEAPEST = 'cheapest';
    case LEAST_WALKING = 'least_walking';
    case FEWEST_TRANSFERS = 'fewest_transfers';

    public function label(): string
    {
        return match ($this) {
            self::FASTEST => 'Tercepat',
            self::CHEAPEST => 'Termurah',
            self::LEAST_WALKING => 'Paling Sedikit Jalan Kaki',
            self::FEWEST_TRANSFERS => 'Paling Sedikit Transit',
        };
    }

    public function badgeText(): string
    {
        return match ($this) {
            self::FASTEST => 'TERCEPAT',
            self::CHEAPEST => 'TERMURAH',
            self::LEAST_WALKING => 'MINIM JALAN KAKI',
            self::FEWEST_TRANSFERS => 'LANGSUNG',
        };
    }
}
