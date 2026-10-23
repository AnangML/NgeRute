<?php

declare(strict_types=1);

namespace App\Services\Fares\Strategies;

use App\Services\Fares\Contracts\FareStrategyInterface;

final class MrtFareStrategy implements FareStrategyInterface
{
    private const BASE_FARE = 3000;
    private const FARE_PER_STATION = 1000;
    private const MAX_FARE = 14000;

    public function calculateFare(int $distanceMeters, int $stopsCount = 1, array $options = []): int
    {
        if ($stopsCount <= 0) {
            return 0;
        }

        // MRT Jakarta: Base Rp3.000 for 1st station hop + Rp1.000 per additional hop, capped at Rp14.000
        $hops = max(1, $stopsCount);
        $fare = self::BASE_FARE + (($hops - 1) * self::FARE_PER_STATION);

        return min($fare, self::MAX_FARE);
    }

    public function supportsOperator(string $operatorCode): bool
    {
        return strtoupper($operatorCode) === 'MRTJ';
    }
}
