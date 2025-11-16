<?php

namespace App\Providers;

use App\Models\PracticePackage;
use App\Observers\PracticePackageObserver;
use App\Support\Integrations\IntegrationConfigurator;
use App\Support\Telemetry\Drivers\Ga4Driver;
use App\Support\Telemetry\Drivers\MixpanelDriver;
use App\Support\Telemetry\TelemetrySyncService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelemetrySyncService::class, function ($app) {
            return new TelemetrySyncService([
                $app->make(Ga4Driver::class),
                $app->make(MixpanelDriver::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        IntegrationConfigurator::apply();
        URL::defaults(['locale' => config('app.locale', 'es')]);
        PracticePackage::observe(PracticePackageObserver::class);
    }
}

