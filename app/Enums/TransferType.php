<?php

declare(strict_types=1);

namespace App\Enums;

enum TransferType: string
{
    case IN_STATION = 'in_station';
    case BRIDGE_JPO = 'bridge_jpo';
    case STREET_WALK = 'street_walk';
    case UNDERGROUND = 'underground';

    public function label(): string
    {
        return match ($this) {
            self::IN_STATION => 'Transfer Dalam Stasiun',
            self::BRIDGE_JPO => 'Jembatan Penyeberangan (JPO/JPM)',
            self::STREET_WALK => 'Jalan Kaki Terbuka',
            self::UNDERGROUND => 'Terowongan / Jalur Bawah Tanah',
        };
    }
}
