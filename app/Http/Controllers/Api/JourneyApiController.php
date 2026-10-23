<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\RouteNotFoundException;
use App\Exceptions\StationNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlanJourneyRequest;
use App\Services\Routing\JourneyPlanner;
use Illuminate\Http\JsonResponse;

final class JourneyApiController extends Controller
{
    public function __construct(
        private readonly JourneyPlanner $journeyPlanner,
    ) {}

    /**
     * Calculate multimodal routes between origin and destination.
     */
    public function plan(PlanJourneyRequest $request): JsonResponse
    {
        try {
            $dto = $request->toDto();
            $result = $this->journeyPlanner->plan($dto);

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
                'meta' => [
                    'routes_count' => count($result->routes),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (RouteNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ROUTE_NOT_FOUND',
                    'message' => $e->getMessage(),
                ],
            ], 404);
        } catch (StationNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'STATION_NOT_FOUND',
                    'message' => $e->getMessage(),
                ],
            ], 404);
        }
    }
}
