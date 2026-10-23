<?php

declare(strict_types=1);

namespace App\Services\Fares\Strategies;

use App\Services\Fares\Contracts\FareStrategyInterface;

final class LrtFareStrategy implements FareStrategyInterface
{
    private const BASE_FARE = 5000;
    private const BASE_KM = 1.0;
    private const FARE_PER_KM = 700;
    private const MAX_FARE_DEFAULT = 20000;

    public function calculateFare(int $distanceMeters, int $stopsCount = 1, array $options = []): int
    {
        $distanceKm = max(1.0, $distanceMeters / 1000.0);

        if ($distanceKm <= self::BASE_KM) {
            return self::BASE_FARE;
        }

        $excessKm = $distanceKm - self::BASE_KM;
        $fare = self::BASE_FARE + (int) round($excessKm * self::FARE_PER_KM);

        return min($fare, self::MAX_FARE_DEFAULT);
    }

    public function supportsOperator(string $operatorCode): bool
    {
        return in_array(strtoupper($operatorCode), ['LRTJBD', 'LRTJ', 'LRT'], true);
    }
}
