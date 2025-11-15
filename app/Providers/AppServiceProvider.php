<?php

namespace App\Providers;

use App\Support\Integrations\IntegrationConfigurator;
use Illuminate\Support\Facades\URL;
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
        IntegrationConfigurator::apply();
        URL::defaults(['locale' => config('app.locale', 'es')]);
    }
}
