<?php

declare(strict_types=1);

namespace App\Services\Fares;

use App\Services\Fares\Contracts\FareStrategyInterface;
use App\Services\Fares\Strategies\KrlFareStrategy;
use App\Services\Fares\Strategies\LrtFareStrategy;
use App\Services\Fares\Strategies\MrtFareStrategy;
use App\Services\Fares\Strategies\TransJakartaFareStrategy;

final class FareCalculator
{
    /**
     * @var list<FareStrategyInterface>
     */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new MrtFareStrategy(),
            new KrlFareStrategy(),
            new LrtFareStrategy(),
            new TransJakartaFareStrategy(),
        ];
    }

    /**
     * Calculate fare for a specific operator and trip segment.
     */
    public function calculate(?string $operatorCode, int $distanceMeters, int $stopsCount = 1, array $options = []): int
    {
        if ($operatorCode === null || $stopsCount <= 0) {
            return 0; // Walking transfers have 0 fare
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->supportsOperator($operatorCode)) {
                return $strategy->calculateFare($distanceMeters, $stopsCount, $options);
            }
        }

        return 0; // Default if unverified or unknown
    }
}
