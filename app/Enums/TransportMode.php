<?php

declare(strict_types=1);

namespace App\Enums;

enum TransportMode: string
{
    case RAIL = 'rail';
    case SUBWAY = 'subway';
    case LRT = 'lrt';
    case BUS = 'bus';
    case WALK = 'walk';

    public function label(): string
    {
        return match ($this) {
            self::RAIL => 'KRL Commuter Line',
            self::SUBWAY => 'MRT Jakarta',
            self::LRT => 'LRT',
            self::BUS => 'TransJakarta',
            self::WALK => 'Jalan Kaki',
        };
    }

    public function defaultColorHex(): string
    {
        return match ($this) {
            self::RAIL => '#DC2626',
            self::SUBWAY => '#005BAC',
            self::LRT => '#0284C7',
            self::BUS => '#0077C8',
            self::WALK => '#64748B',
        };
    }
}
