<?php

declare(strict_types=1);

use App\Models\PaymentEvent;
use App\Models\Tier;
use App\Models\User;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logPayments(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logPayments('Admin QA no disponible.');

    return;
}

Auth::login($admin);

$tiers = Tier::whereIn('slug', ['pro', 'vip'])->get()->keyBy('slug');

if ($tiers->isEmpty()) {
    logPayments('No hay tiers pro/vip configurados.');

    return;
}

$students = User::role('student_paid')->limit(3)->get();

if ($students->isEmpty()) {
    logPayments('No se encontraron estudiantes paid para simular.');

    return;
}

$simulator = app(PaymentSimulator::class);

foreach ($students as $index => $student) {
    $tier = $index % 2 === 0
        ? $tiers->get('pro')
        : $tiers->get('vip', $tiers->first());

    if (! $tier) {
        continue;
    }

    $payload = [
        'provider' => 'simulator',
        'status' => 'active',
        'amount' => ($tier->price_monthly ?? 49) + ($index * 3),
        'currency' => $tier->currency ?? 'USD',
        'metadata' => [
            'origin' => 'admin_payments_flow',
            'batch' => now()->format('Ymd_His'),
        ],
    ];

    $subscription = $simulator->simulate($student, $tier, $payload);

    logPayments(sprintf(
        'Suscripción simulada: user %s -> tier %s (subscription #%d).',
        $student->email,
        $tier->name,
        $subscription->id
    ));
}

$recentEvents = PaymentEvent::query()
    ->with(['user:id,email', 'tier:id,name'])
    ->latest()
    ->limit(5)
    ->get()
    ->map(fn (PaymentEvent $event) => [
        'id' => $event->id,
        'user' => $event->user?->email,
        'tier' => $event->tier?->name,
        'status' => $event->status,
        'provider' => $event->provider,
        'amount' => $event->amount,
        'currency' => $event->currency,
        'created_at' => optional($event->created_at)->toDateTimeString(),
    ]);

logPayments('Eventos recientes: '.json_encode($recentEvents, JSON_PRETTY_PRINT));

logPayments('Flujo Payment Simulator finalizado.');

