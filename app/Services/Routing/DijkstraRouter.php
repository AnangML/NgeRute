<?php

declare(strict_types=1);

namespace App\Services\Routing;

use App\DTOs\TransitEdgeDto;
use App\Enums\RouteCriteria;
use SplPriorityQueue;

final class DijkstraRouter
{
    /**
     * Finds the shortest path in the graph according to criteria.
     *
     * @param array<int, list<TransitEdgeDto>> $graph Adjacency list keyed by stop_id
     * @return list<TransitEdgeDto> Sequence of edges from origin to destination, or empty array if unreachable
     */
    public function findPath(
        array $graph,
        int $originStopId,
        int $destinationStopId,
        RouteCriteria $criteria = RouteCriteria::FASTEST
    ): array {
        if ($originStopId === $destinationStopId) {
            return [];
        }

        if (!isset($graph[$originStopId]) || !isset($graph[$destinationStopId])) {
            return [];
        }

        /** @var array<int, float> $distances Best cost to reach node */
        $distances = [];
        /** @var array<int, TransitEdgeDto> $predecessorEdge Predecessor edge leading to node */
        $predecessorEdge = [];
        /** @var array<int, int> $predecessorNode Predecessor stop_id leading to node */
        $predecessorNode = [];

        // Min-Priority Queue implementation via negative weights
        $pq = new class extends SplPriorityQueue {
            public function compare(mixed $priority1, mixed $priority2): int
            {
                // Inverted comparison for Min-Heap
                return (float) $priority1 > (float) $priority2 ? -1 : 1;
            }
        };

        $distances[$originStopId] = 0.0;
        $pq->insert($originStopId, 0.0);

        while (!$pq->isEmpty()) {
            /** @var int $u */
            $u = $pq->extract();

            if ($u === $destinationStopId) {
                break; // Reached target with shortest cost
            }

            $currentDist = $distances[$u] ?? INF;
            $prevEdge = $predecessorEdge[$u] ?? null;

            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge->toStopId;
                $edgeCost = $this->calculateEdgeCost($edge, $prevEdge, $criteria);
                $newDist = $currentDist + $edgeCost;

                if (!isset($distances[$v]) || $newDist < $distances[$v]) {
                    $distances[$v] = $newDist;
                    $predecessorEdge[$v] = $edge;
                    $predecessorNode[$v] = $u;
                    $pq->insert($v, $newDist);
                }
            }
        }

        if (!isset($predecessorEdge[$destinationStopId])) {
            return []; // No path found
        }

        // Backtrack path from destination to origin
        $path = [];
        $curr = $destinationStopId;

        while ($curr !== $originStopId) {
            if (!isset($predecessorEdge[$curr])) {
                return [];
            }
            $edge = $predecessorEdge[$curr];
            array_unshift($path, $edge);
            $curr = $edge->fromStopId;
        }

        return $path;
    }

    private function calculateEdgeCost(
        TransitEdgeDto $edge,
        ?TransitEdgeDto $prevEdge,
        RouteCriteria $criteria
    ): float {
        $isTransfer = $edge->isTransfer();
        $isModeSwitch = $prevEdge !== null && (
            $prevEdge->routeId !== $edge->routeId ||
            $prevEdge->operatorCode !== $edge->operatorCode ||
            $isTransfer
        );

        return match ($criteria) {
            RouteCriteria::FASTEST => $this->costForFastest($edge, $isModeSwitch),
            RouteCriteria::CHEAPEST => $this->costForCheapest($edge, $isModeSwitch),
            RouteCriteria::LEAST_WALKING => $this->costForLeastWalking($edge, $isModeSwitch),
            RouteCriteria::FEWEST_TRANSFERS => $this->costForFewestTransfers($edge, $isModeSwitch),
        };
    }

    private function costForFastest(TransitEdgeDto $edge, bool $isModeSwitch): float
    {
        $cost = (float) $edge->durationSeconds;

        // Add 180s penalty for transfer friction (waiting / switching trains)
        if ($isModeSwitch) {
            $cost += 180.0;
        }

        return $cost;
    }

    private function costForCheapest(TransitEdgeDto $edge, bool $isModeSwitch): float
    {
        // Tie-breaker by duration
        $durationTieBreaker = (float) $edge->durationSeconds * 0.05;

        if ($edge->isTransfer()) {
            return $durationTieBreaker;
        }

        // Penalty when boarding another operator or starting a new line
        $fareCost = $isModeSwitch ? 3000.0 : 1000.0;

        return $fareCost + $durationTieBreaker;
    }

    private function costForLeastWalking(TransitEdgeDto $edge, bool $isModeSwitch): float
    {
        $cost = (float) $edge->durationSeconds;

        if ($edge->isTransfer()) {
            // Heavily penalize walking meters so the router prefers rail/bus alternatives
            $cost += ((float) $edge->distanceMeters * 10.0);
        }

        if ($isModeSwitch) {
            $cost += 120.0;
        }

        return $cost;
    }

    private function costForFewestTransfers(TransitEdgeDto $edge, bool $isModeSwitch): float
    {
        $cost = (float) $edge->durationSeconds;

        if ($isModeSwitch) {
            // Massive penalty for any route switch to maintain direct journey
            $cost += 50000.0;
        }

        return $cost;
    }
}
