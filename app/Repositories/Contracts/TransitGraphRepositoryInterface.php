<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\RouteSegment;
use App\Models\Stop;
use App\Models\Transfer;
use Illuminate\Support\Collection;

interface TransitGraphRepositoryInterface
{
    /**
     * @return Collection<int, Stop>
     */
    public function getAllStops(): Collection;

    /**
     * @return Collection<int, RouteSegment>
     */
    public function getAllRouteSegments(): Collection;

    /**
     * @return Collection<int, Transfer>
     */
    public function getAllTransfers(): Collection;
}
