<?php

declare(strict_types=1);

use App\Http\Controllers\Api\JourneyApiController;
use App\Http\Controllers\Api\StationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Stations & Halte
    Route::get('/stations', [StationApiController::class, 'all'])->name('api.stations.all');
    Route::get('/stations/search', [StationApiController::class, 'search'])->name('api.stations.search');
    Route::get('/stations/nearby', [StationApiController::class, 'nearby'])->name('api.stations.nearby');

    // Multimodal Journey Planning
    Route::get('/journey/plan', [JourneyApiController::class, 'plan'])->name('api.journey.plan');
});
