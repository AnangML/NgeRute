<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Stop;
use Tests\TestCase;

final class StationApiTest extends TestCase
{
    public function test_can_list_all_active_stations(): void
    {
        $response = $this->getJson('/api/v1/stations');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'code', 'name', 'latitude', 'longitude', 'mode', 'operator_name', 'color_hex'],
                ],
                'meta' => ['count', 'timestamp'],
            ]);
    }

    public function test_can_search_stations_by_keyword(): void
    {
        $response = $this->getJson('/api/v1/stations/search?q=dukuh');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertStringContainsStringIgnoringCase('dukuh', $data[0]['name']);
    }

    public function test_can_find_nearby_stations_spatially(): void
    {
        // Coordinates of Dukuh Atas Hub
        $response = $this->getJson('/api/v1/stations/nearby?lat=-6.200833&lng=106.822778&radius=1000');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('distance_meters', $data[0]);
        $this->assertLessThanOrEqual(50, $data[0]['distance_meters']); // Should be Dukuh Atas itself
    }

    public function test_validates_search_parameters(): void
    {
        $response = $this->getJson('/api/v1/stations/search');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    }
}
