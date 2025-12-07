<?php

declare(strict_types=1);

use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logAssignments(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logAssignments('Admin QA no disponible.');

    return;
}

Auth::login($admin);

$pending = AssignmentSubmission::query()
    ->where('status', 'submitted')
    ->limit(4)
    ->get();

foreach ($pending as $submission) {
    $score = rand(75, 95);
    $submission->update([
        'status' => 'graded',
        'score' => $score,
        'max_points' => $submission->max_points ?: 100,
        'feedback' => 'Evaluación automática por flujo QA. Buen trabajo.',
        'graded_at' => now(),
    ]);

    logAssignments("Submission {$submission->id} para assignment {$submission->assignment_id} calificado con {$score}.");
}

$graded = AssignmentSubmission::query()
    ->where('status', 'graded')
    ->whereNull('approved_at')
    ->limit(3)
    ->get();

foreach ($graded as $submission) {
    $submission->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    logAssignments("Submission {$submission->id} aprobado oficialmente.");
}

$stats = AssignmentSubmission::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status')
    ->toArray();

logAssignments('Resumen de estados: '.json_encode($stats));

logAssignments('Flujo Admin Assignments finalizado.');

