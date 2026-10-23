<?php

declare(strict_types=1);

namespace App\Services\Fares\Strategies;

use App\Services\Fares\Contracts\FareStrategyInterface;

final class TransJakartaFareStrategy implements FareStrategyInterface
{
    private const STANDARD_FLAT_FARE = 3500;
    private const EARLY_BIRD_FARE = 2000;

    public function calculateFare(int $distanceMeters, int $stopsCount = 1, array $options = []): int
    {
        $isEarlyBird = (bool) ($options['is_early_bird'] ?? false);

        return $isEarlyBird ? self::EARLY_BIRD_FARE : self::STANDARD_FLAT_FARE;
    }

    public function supportsOperator(string $operatorCode): bool
    {
        return strtoupper($operatorCode) === 'TJ';
    }
}
