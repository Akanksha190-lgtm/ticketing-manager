<?php

namespace App\Providers;

use App\Models\AirlineCommission;
use App\Models\FareCommissionEntries;
use Illuminate\Support\Facades\View;
use App\Observers\FareCommissionEntriesObserver;
use App\Observers\AirlineCommissionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FareCommissionEntries::observe(FareCommissionEntriesObserver::class);
        AirlineCommission::observe(AirlineCommissionObserver::class);

        View::composer('layouts.navbar', function ($view) {

            $activeFares = FareCommissionEntries::where('status', 'Active')->count();

            $expiringFares = FareCommissionEntries::where('status', 'Expiring Soon')->count();

            $carriers = AirlineCommission::whereNotNull('airline')
                ->where('airline', '!=', '')
                ->distinct()
                ->count('airline');

            $view->with([
                'activeFares' => $activeFares,
                'expiringFares' => $expiringFares,
                'carriers' => $carriers,
            ]);
        });
    }
}
