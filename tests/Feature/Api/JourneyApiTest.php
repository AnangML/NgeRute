<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Stop;
use Tests\TestCase;

final class JourneyApiTest extends TestCase
{
    public function test_can_plan_direct_mrt_journey(): void
    {
        $lebakBulus = Stop::where('code', 'LBB')->firstOrFail();
        $dukuhAtas = Stop::where('code', 'DKA')->firstOrFail();

        $response = $this->getJson("/api/v1/journey/plan?origin_id={$lebakBulus->id}&destination_id={$dukuhAtas->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'origin',
                    'destination',
                    'routes' => [
                        '*' => [
                            'id',
                            'primary_criteria',
                            'badges',
                            'total_duration_minutes',
                            'formatted_fare',
                            'segments',
                            'full_polyline',
                        ],
                    ],
                    'provenance',
                ],
            ]);

        $routes = $response->json('data.routes');
        $this->assertNotEmpty($routes);
        $this->assertSame(0, $routes[0]['transfer_count']);
    }

    public function test_can_plan_multimodal_transfer_journey(): void
    {
        // Lebak Bulus (MRT) to Manggarai (KRL) requires walking transfer at Dukuh Atas/Sudirman
        $lebakBulus = Stop::where('code', 'LBB')->firstOrFail();
        $manggarai = Stop::where('code', 'MRI')->firstOrFail();

        $response = $this->getJson("/api/v1/journey/plan?origin_id={$lebakBulus->id}&destination_id={$manggarai->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $routes = $response->json('data.routes');
        $this->assertNotEmpty($routes);
        $this->assertGreaterThanOrEqual(1, $routes[0]['transfer_count']);

        // Check that segments contain both transit and transfer
        $segmentTypes = array_column($routes[0]['segments'], 'type');
        $this->assertContains('transit', $segmentTypes);
        $this->assertContains('transfer', $segmentTypes);
    }

    public function test_validates_same_origin_and_destination(): void
    {
        $stop = Stop::firstOrFail();

        $response = $this->getJson("/api/v1/journey/plan?origin_id={$stop->id}&destination_id={$stop->id}");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['destination_id']);
    }
}
