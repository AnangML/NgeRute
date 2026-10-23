<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\TransitEdgeDto;
use App\Enums\RouteCriteria;
use App\Services\Routing\DijkstraRouter;
use PHPUnit\Framework\TestCase;

final class DijkstraRouterTest extends TestCase
{
    private DijkstraRouter $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new DijkstraRouter();
    }

    public function test_finds_direct_shortest_path(): void
    {
        // Simple linear graph: 1 -> 2 -> 3
        $graph = [
            1 => [
                new TransitEdgeDto(
                    fromStopId: 1,
                    toStopId: 2,
                    distanceMeters: 1000,
                    durationSeconds: 120,
                    edgeType: 'transit',
                    routeId: 10,
                    operatorCode: 'MRTJ'
                ),
            ],
            2 => [
                new TransitEdgeDto(
                    fromStopId: 2,
                    toStopId: 3,
                    distanceMeters: 1000,
                    durationSeconds: 120,
                    edgeType: 'transit',
                    routeId: 10,
                    operatorCode: 'MRTJ'
                ),
            ],
            3 => [],
        ];

        $path = $this->router->findPath($graph, 1, 3, RouteCriteria::FASTEST);

        $this->assertCount(2, $path);
        $this->assertSame(1, $path[0]->fromStopId);
        $this->assertSame(2, $path[0]->toStopId);
        $this->assertSame(2, $path[1]->fromStopId);
        $this->assertSame(3, $path[1]->toStopId);
    }

    public function test_returns_empty_when_target_unreachable(): void
    {
        $graph = [
            1 => [
                new TransitEdgeDto(
                    fromStopId: 1,
                    toStopId: 2,
                    distanceMeters: 500,
                    durationSeconds: 60,
                    edgeType: 'transit'
                ),
            ],
            2 => [],
            99 => [], // Disconnected island
        ];

        $path = $this->router->findPath($graph, 1, 99, RouteCriteria::FASTEST);
        $this->assertEmpty($path);
    }

    public function test_favors_direct_route_under_fewest_transfers(): void
    {
        // 1 can reach 3 via:
        // Option A: 1 -> 3 (direct slow route, 400s, route 1)
        // Option B: 1 -> 2 -> 3 (fast transfer route, 100s + 100s, route 1 -> transfer -> route 2)
        $graph = [
            1 => [
                new TransitEdgeDto(
                    fromStopId: 1,
                    toStopId: 3,
                    distanceMeters: 5000,
                    durationSeconds: 400,
                    edgeType: 'transit',
                    routeId: 1,
                    operatorCode: 'BUS'
                ),
                new TransitEdgeDto(
                    fromStopId: 1,
                    toStopId: 2,
                    distanceMeters: 2000,
                    durationSeconds: 100,
                    edgeType: 'transit',
                    routeId: 2,
                    operatorCode: 'MRTJ'
                ),
            ],
            2 => [
                new TransitEdgeDto(
                    fromStopId: 2,
                    toStopId: 3,
                    distanceMeters: 200,
                    durationSeconds: 100,
                    edgeType: 'transfer',
                    mode: 'walk'
                ),
            ],
            3 => [],
        ];

        // FEWEST_TRANSFERS should pick Option A (1 -> 3) despite longer travel duration
        $pathFewest = $this->router->findPath($graph, 1, 3, RouteCriteria::FEWEST_TRANSFERS);
        $this->assertCount(1, $pathFewest);
        $this->assertSame(3, $pathFewest[0]->toStopId);
        $this->assertSame(1, $pathFewest[0]->routeId);

        // FASTEST should pick Option B (1 -> 2 -> 3) because 100 + 100 + penalty(180) = 380s < 400s
        $pathFastest = $this->router->findPath($graph, 1, 3, RouteCriteria::FASTEST);
        $this->assertCount(2, $pathFastest);
        $this->assertSame(2, $pathFastest[0]->toStopId);
        $this->assertSame(3, $pathFastest[1]->toStopId);
    }
}
