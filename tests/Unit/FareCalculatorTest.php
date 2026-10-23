<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Fares\FareCalculator;
use PHPUnit\Framework\TestCase;

final class FareCalculatorTest extends TestCase
{
    private FareCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FareCalculator();
    }

    public function test_mrt_fare_scales_correctly_and_respects_cap(): void
    {
        // 1 station hop: Base fare Rp3.000
        $fare1 = $this->calculator->calculate('MRTJ', 2000, 1);
        $this->assertSame(3000, $fare1);

        // 6 station hops: Rp3.000 + 5 * 1.000 = Rp8.000
        $fare6 = $this->calculator->calculate('MRTJ', 8000, 6);
        $this->assertSame(8000, $fare6);

        // 13 station hops: Capped at Rp14.000
        $fareMax = $this->calculator->calculate('MRTJ', 15000, 13);
        $this->assertSame(14000, $fareMax);
    }

    public function test_krl_fare_distance_tiers(): void
    {
        // Under 25km: Base fare Rp3.000
        $fareShort = $this->calculator->calculate('KCI', 15000, 4);
        $this->assertSame(3000, $fareShort);

        // 32km: Rp3.000 + (ceil((32-25)/10) * 1000) = Rp4.000
        $fareMedium = $this->calculator->calculate('KCI', 32000, 8);
        $this->assertSame(4000, $fareMedium);
    }

    public function test_transjakarta_flat_fare(): void
    {
        $fareNormal = $this->calculator->calculate('TJ', 12000, 5);
        $this->assertSame(3500, $fareNormal);

        $fareEarly = $this->calculator->calculate('TJ', 12000, 5, ['is_early_bird' => true]);
        $this->assertSame(2000, $fareEarly);
    }

    public function test_walking_transfers_have_zero_fare(): void
    {
        $this->assertSame(0, $this->calculator->calculate(null, 300, 0));
        $this->assertSame(0, $this->calculator->calculate('WALK', 300, 0));
    }
}
