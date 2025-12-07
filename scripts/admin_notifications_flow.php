<?php

declare(strict_types=1);

use App\Models\Message;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\SimulatedPaymentNotification;
use App\Notifications\StudentMessageNotification;
use App\Notifications\TeacherMessageNotification;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logNotifications(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();
$teacher = User::role('teacher')->first();
$student = User::role('student_paid')->first();

if (! $admin || ! $teacher || ! $student) {
    logNotifications('Faltan usuarios para el flujo de notificaciones.');

    return;
}

Auth::login($admin);

$message = Message::create([
    'sender_id' => $admin->id,
    'type' => 'announcement',
    'subject' => 'Aviso QA adicional',
    'body' => 'Este mensaje fue generado por admin_notifications_flow para probar las notificaciones.',
    'notify_email' => true,
    'sent_at' => now(),
]);

Notification::send($teacher, new TeacherMessageNotification($message));
logNotifications("TeacherMessageNotification enviada a {$teacher->email}.");

Notification::send($student, new StudentMessageNotification($message));
logNotifications("StudentMessageNotification enviada a {$student->email}.");

$simulator = app(PaymentSimulator::class);
$tier = $student->activeTiers()->first() ?? $student->tiers()->first();

if (! $tier) {
    $tier = Tier::first();

    if ($tier) {
        $student->tiers()->syncWithoutDetaching([
            $tier->id => [
                'status' => 'active',
                'source' => 'admin_notifications_flow',
                'assigned_by' => $admin->id,
                'starts_at' => now(),
            ],
        ]);
    }
}

if ($tier) {
    $subscription = $simulator->simulate($student, $tier, [
        'status' => 'active',
        'provider' => 'simulator',
        'amount' => $tier->pivot->amount ?? 59,
        'currency' => $tier->pivot->currency ?? 'USD',
        'metadata' => ['origin' => 'admin_notifications_flow'],
    ]);

    Notification::send($student, new SimulatedPaymentNotification($subscription));
    logNotifications("SimulatedPaymentNotification enviada a {$student->email}.");
} else {
    logNotifications('El estudiante no tiene tier activo para enviar SimulatedPaymentNotification.');
}

logNotifications('Flujo Admin Notifications finalizado.');

