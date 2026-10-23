<?php

declare(strict_types=1);

namespace App\Services\Fares\Contracts;

interface FareStrategyInterface
{
    /**
     * Calculate fare for a journey segment or trip.
     *
     * @param int $distanceMeters Total distance in meters
     * @param int $stopsCount Number of stations passed
     * @param array<string, mixed> $options Additional contextual options (e.g., peak/off-peak)
     */
    public function calculateFare(int $distanceMeters, int $stopsCount = 1, array $options = []): int;

    public function supportsOperator(string $operatorCode): bool;
}
