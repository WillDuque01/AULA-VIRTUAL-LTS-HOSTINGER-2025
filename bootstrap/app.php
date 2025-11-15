<?php

use App\Console\Commands\CredentialsAudit;
use App\Console\Commands\CredentialsCheck;
use App\Console\Commands\SimulatePayment;
use App\Console\Commands\StorageMigrate;
use App\Http\Middleware\EnsureSetupIsComplete;
use App\Http\Middleware\SecurityHeaders;
use App\Providers\EventServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        CredentialsCheck::class,
        StorageMigrate::class,
        CredentialsAudit::class,
        SimulatePayment::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web([
            \App\Http\Middleware\SetLocale::class,
            EnsureSetupIsComplete::class,
            SecurityHeaders::class,
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
