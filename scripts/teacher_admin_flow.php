<?php

declare(strict_types=1);

use App\Models\DiscordPractice;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\PracticePackage;
use App\Models\TeacherSubmission;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logTeacherAdmin(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$teacherAdmin = User::where('email', 'teacher.admin.qa@letstalkspanish.io')->first();

if (! $teacherAdmin || ! $teacherAdmin->hasRole('teacher_admin')) {
    logTeacherAdmin('Teacher Admin QA no disponible.');

    return;
}

Auth::login($teacherAdmin);

$practice = DiscordPractice::create([
    'lesson_id' => 1,
    'type' => 'group',
    'title' => 'TeacherAdmin Practice '.Str::upper(Str::random(3)),
    'description' => 'Sesión creada por teacher_admin_flow.',
    'start_at' => now()->addDays(2)->setTime(17, 0),
    'end_at' => now()->addDays(2)->setTime(18, 0),
    'duration_minutes' => 60,
    'capacity' => 6,
    'discord_channel_url' => 'https://discord.gg/qa-lab',
    'status' => 'scheduled',
    'created_by' => $teacherAdmin->id,
]);

logTeacherAdmin("Práctica creada: {$practice->title} (ID {$practice->id}).");

$package = PracticePackage::create([
    'creator_id' => $teacherAdmin->id,
    'title' => 'Teacher Admin Pack '.Str::upper(Str::random(2)),
    'subtitle' => 'Generado por teacher_admin_flow.',
    'description' => 'Pack de práctica administrado por un teacher admin.',
    'sessions_count' => 4,
    'price_amount' => 89,
    'price_currency' => 'USD',
    'is_global' => false,
    'visibility' => 'private',
    'delivery_platform' => 'zoom',
    'delivery_url' => 'https://zoom.us/j/qa-admin',
    'status' => 'pending',
]);

logTeacherAdmin("Practice package creado en estado {$package->status}.");

$submission = TeacherSubmission::create([
    'user_id' => $teacherAdmin->id,
    'course_id' => 1,
    'chapter_id' => null,
    'type' => 'module',
    'title' => 'Plantilla de cohortes QA',
    'summary' => 'Teacher Admin propone un nuevo módulo.',
    'payload' => ['notes' => 'Generado por teacher_admin_flow'],
    'status' => 'pending',
]);

logTeacherAdmin("Propuesta registrada (submission {$submission->id}).");

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if ($admin) {
    $message = Message::create([
        'sender_id' => $teacherAdmin->id,
        'subject' => 'Reporte QA Teacher Admin',
        'body' => 'Mensaje enviado desde el flujo teacher_admin_flow.',
        'notify_email' => false,
        'sent_at' => now(),
    ]);

    MessageRecipient::create([
        'message_id' => $message->id,
        'user_id' => $admin->id,
        'status' => 'sent',
    ]);

    logTeacherAdmin("Mensaje enviado al admin (message {$message->id}).");
}

$stats = [
    'practices_by_teacher_admin' => DiscordPractice::where('created_by', $teacherAdmin->id)->count(),
    'pending_submissions' => TeacherSubmission::where('status', 'pending')->count(),
];

logTeacherAdmin('Resumen Teacher Admin: '.json_encode($stats));
logTeacherAdmin('Flujo Teacher Admin finalizado.');

