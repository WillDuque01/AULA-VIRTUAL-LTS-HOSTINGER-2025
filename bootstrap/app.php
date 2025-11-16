<?php

use App\Console\Commands\CredentialsAudit;
use App\Console\Commands\CredentialsCheck;
use App\Console\Commands\RetryIntegrationEvents;
use App\Console\Commands\SimulatePayment;
use App\Console\Commands\StorageMigrate;
use App\Http\Middleware\EnsureSetupIsComplete;
use App\Http\Middleware\SecurityHeaders;
use App\Providers\EventServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        CredentialsCheck::class,
        StorageMigrate::class,
        CredentialsAudit::class,
        SimulatePayment::class,
        RetryIntegrationEvents::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('integration:retry failed')
            ->dailyAt('02:00')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
            EnsureSetupIsComplete::class,
        ]);

        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        $middleware->alias([
            'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->removeFromGroup('web', [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->priority([
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withProviders([
        EventServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
