<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\StationRepositoryInterface::class,
            \App\Repositories\Eloquent\PgSqlStationRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TransitGraphRepositoryInterface::class,
            \App\Repositories\Eloquent\PgSqlTransitGraphRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
