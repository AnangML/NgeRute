<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\StationDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchStationRequest;
use App\Repositories\Contracts\StationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StationApiController extends Controller
{
    public function __construct(
        private readonly StationRepositoryInterface $stationRepository,
    ) {}

    /**
     * Search stations by keyword.
     */
    public function search(SearchStationRequest $request): JsonResponse
    {
        $query = (string) $request->validated('q');
        $limit = (int) ($request->validated('limit') ?? 10);

        $stations = $this->stationRepository->search($query, $limit);

        return response()->json([
            'success' => true,
            'data' => $stations->map(fn (StationDto $s) => $s->toArray())->values(),
            'meta' => [
                'count' => $stations->count(),
                'query' => $query,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Find nearby stations using PostgreSQL spatial query.
     */
    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:50', 'max:5000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (int) ($validated['radius'] ?? 1000);
        $limit = (int) ($validated['limit'] ?? 5);

        $stations = $this->stationRepository->findNearby($lat, $lng, $radius, $limit);

        return response()->json([
            'success' => true,
            'data' => $stations->map(fn (StationDto $s) => $s->toArray())->values(),
            'meta' => [
                'count' => $stations->count(),
                'center' => ['latitude' => $lat, 'longitude' => $lng],
                'radius_meters' => $radius,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get all active stations for map layers.
     */
    public function all(): JsonResponse
    {
        $stations = $this->stationRepository->getAllActive();

        return response()->json([
            'success' => true,
            'data' => $stations->map(fn (StationDto $s) => $s->toArray())->values(),
            'meta' => [
                'count' => $stations->count(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
