<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\RouteSegment;
use App\Models\Stop;
use App\Models\Transfer;
use App\Repositories\Contracts\TransitGraphRepositoryInterface;
use Illuminate\Support\Collection;

final class PgSqlTransitGraphRepository implements TransitGraphRepositoryInterface
{
    public function getAllStops(): Collection
    {
        return Stop::with('operator')->where('is_active', true)->get();
    }

    public function getAllRouteSegments(): Collection
    {
        return RouteSegment::with(['route.operator', 'fromStop', 'toStop'])
            ->whereHas('route', fn ($q) => $q->where('is_active', true))
            ->get();
    }

    public function getAllTransfers(): Collection
    {
        return Transfer::with(['fromStop', 'toStop'])->get();
    }
}
