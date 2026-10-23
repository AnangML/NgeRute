<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DataProvenanceLog;
use App\Repositories\Contracts\StationRepositoryInterface;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function __construct(
        private readonly StationRepositoryInterface $stationRepository,
    ) {}

    public function index(): View
    {
        $stations = $this->stationRepository->getAllActive();
        $latestProvenance = DataProvenanceLog::latest()->first();

        return view('home', [
            'stations' => $stations,
            'latestProvenance' => $latestProvenance,
        ]);
    }
}
