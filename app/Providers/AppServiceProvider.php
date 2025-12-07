<?php

namespace App\Providers;

use App\Models\CohortRegistration;
use App\Models\CohortTemplate;
use App\Models\PracticePackage;
use App\Models\User;
use App\Observers\CohortRegistrationObserver;
use App\Observers\CohortTemplateObserver;
use App\Observers\PracticePackageObserver;
use App\Support\Integrations\IntegrationConfigurator;
use App\Support\Telemetry\Drivers\Ga4Driver;
use App\Support\Telemetry\Drivers\MixpanelDriver;
use App\Support\Telemetry\TelemetrySyncService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        CohortTemplate::observe(CohortTemplateObserver::class);
        CohortRegistration::observe(CohortRegistrationObserver::class);

        Gate::define('manage-settings', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->hasAnyRole(['Admin', 'admin', 'teacher_admin', 'support']);
        });

        RateLimiter::for('player-events', function (Request $request) {
            $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(60)->by('player-events:'.$identifier);
        });
    }
}

