<?php

declare(strict_types=1);

namespace App\Services\Fares\Strategies;

use App\Services\Fares\Contracts\FareStrategyInterface;

final class KrlFareStrategy implements FareStrategyInterface
{
    private const BASE_FARE = 3000;
    private const BASE_KM = 25.0;
    private const FARE_PER_NEXT_10KM = 1000;

    public function calculateFare(int $distanceMeters, int $stopsCount = 1, array $options = []): int
    {
        $distanceKm = $distanceMeters / 1000.0;

        if ($distanceKm <= self::BASE_KM) {
            return self::BASE_FARE;
        }

        $excessKm = $distanceKm - self::BASE_KM;
        $tierSteps = (int) ceil($excessKm / 10.0);

        return self::BASE_FARE + ($tierSteps * self::FARE_PER_NEXT_10KM);
    }

    public function supportsOperator(string $operatorCode): bool
    {
        return in_array(strtoupper($operatorCode), ['KCI', 'KRL'], true);
    }
}
