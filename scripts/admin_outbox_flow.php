<?php

declare(strict_types=1);

use App\Models\IntegrationEvent;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logOutbox(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logOutbox('Admin QA no disponible.');

    return;
}

Auth::login($admin);

$events = [
    [
        'event' => 'webhook.course.updated',
        'target' => 'https://hooks.zapier.com/qa/course',
        'payload' => ['course' => 'qa-spanish-lab', 'action' => 'updated'],
        'status' => 'pending',
    ],
    [
        'event' => 'webhook.subscription.created',
        'target' => 'https://hooks.zapier.com/qa/subscription',
        'payload' => ['user' => 'student@letstalkspanish.io'],
        'status' => 'failed',
        'last_error' => 'Timeout simulando error.',
        'attempts' => 2,
        'last_attempt_at' => now()->subMinutes(5),
    ],
    [
        'event' => 'webhook.practice.completed',
        'target' => 'https://hooks.zapier.com/qa/practice',
        'payload' => ['practice_id' => 1],
        'status' => 'sent',
        'sent_at' => now()->subMinutes(2),
        'attempts' => 1,
        'last_attempt_at' => now()->subMinutes(2),
    ],
];

foreach ($events as $data) {
    $record = IntegrationEvent::create(array_merge($data, [
        'payload' => $data['payload'],
    ]));

    logOutbox("Evento {$record->event} creado (status {$record->status}).");
}

$failed = IntegrationEvent::where('status', 'failed')->get();
foreach ($failed as $event) {
    $event->update([
        'status' => 'pending',
        'last_error' => null,
        'attempts' => 0,
    ]);
    logOutbox("Evento {$event->id} reiniciado (status pending).");
}

$summary = IntegrationEvent::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
logOutbox('Resumen outbox: '.json_encode($summary));

logOutbox('Flujo Admin Integration Outbox finalizado.');

