<?php

declare(strict_types=1);

use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logPlanner(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logPlanner('Admin QA no disponible.');

    return;
}

Auth::login($admin);

$lesson = Lesson::whereHas('chapter.course', fn ($query) => $query->where('slug', 'qa-spanish-lab'))
    ->latest('id')
    ->first();

if (! $lesson) {
    logPlanner('No se encontró una lección para crear prácticas.');

    return;
}

$practice = DiscordPractice::create([
    'lesson_id' => $lesson->id,
    'type' => 'group',
    'title' => 'QA Intensive '.Str::upper(Str::random(3)),
    'description' => 'Sesión creada por flujo admin_planner_flow.',
    'cohort_label' => 'QA Flow',
    'practice_package_id' => null,
    'start_at' => Carbon::now()->addDays(4)->setTime(15, 0),
    'end_at' => Carbon::now()->addDays(4)->setTime(16, 0),
    'duration_minutes' => 60,
    'capacity' => 10,
    'discord_channel_url' => 'https://discord.gg/qa-lab',
    'meeting_token' => Str::upper(Str::random(6)),
    'status' => 'scheduled',
    'created_by' => $admin->id,
    'requires_package' => false,
]);

logPlanner("Práctica creada: {$practice->title} (ID {$practice->id}).");

$existing = DiscordPractice::orderBy('start_at')->first();
if ($existing) {
    $existing->update([
        'status' => 'completed',
        'attendance_synced_at' => now(),
    ]);
    logPlanner("Práctica {$existing->id} marcada como completed con attendance_synced_at.");
}

$nextPractice = DiscordPractice::where('id', '!=', $practice->id)->latest('start_at')->first();
if ($nextPractice) {
    $nextPractice->update([
        'status' => 'cancelled',
        'description' => $nextPractice->description.' (cancelada por QA flow)',
    ]);
    logPlanner("Práctica {$nextPractice->id} cancelada para validar acciones administrativas.");
}

$countByStatus = DiscordPractice::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

logPlanner('Estado global de prácticas: '.json_encode($countByStatus));

logPlanner('Flujo Admin Planner finalizado.');

