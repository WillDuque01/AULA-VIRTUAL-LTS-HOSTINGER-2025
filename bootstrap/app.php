<?php

use App\Console\Commands\CredentialsAudit;
use App\Console\Commands\CredentialsCheck;
use App\Console\Commands\MonitorTelemetryBacklogCommand;
use App\Console\Commands\RetryIntegrationEvents;
use App\Console\Commands\SimulatePayment;
use App\Console\Commands\RemindIncompleteProfilesCommand;
use App\Console\Commands\SyncTelemetryCommand;
use App\Console\Commands\SyncPracticeAttendanceSnapshotsCommand;
use App\Console\Commands\StorageMigrate;
use App\Http\Middleware\EnsureSetupIsComplete;
use App\Http\Middleware\PreventResponseCaching;
use App\Http\Middleware\SecurityHeaders;
use App\Providers\EventServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;
use Sentry\Laravel\Tracing\ServiceProvider as SentryTracingServiceProvider;
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
        SyncTelemetryCommand::class,
        SyncPracticeAttendanceSnapshotsCommand::class,
        RemindIncompleteProfilesCommand::class,
        MonitorTelemetryBacklogCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('integration:retry failed')
            ->dailyAt('02:00')
            ->withoutOverlapping();
        $schedule->command('telemetry:sync --limit=500')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('practices:sync-attendance --limit=25')
            ->everyThirtyMinutes()
            ->withoutOverlapping();
        $schedule->command('telemetry:monitor-backlog')
            ->everyFifteenMinutes()
            ->withoutOverlapping();
        $schedule->command('profile:remind-incomplete --threshold=80')
            ->dailyAt('09:00')
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
            EnsureSetupIsComplete::class,
            PreventResponseCaching::class,
        ]);

        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        $middleware->alias([
            'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
            'append.outbox.events' => \App\Http\Middleware\AppendOutboxEvents::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
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
        SentryServiceProvider::class,
        SentryTracingServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
