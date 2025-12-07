<?php

declare(strict_types=1);

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\DiscordPractice;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logStudent(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$students = User::role(['student_paid', 'student_vip', 'student_free'])->limit(18)->get();

if ($students->isEmpty()) {
    logStudent('No hay estudiantes disponibles.');

    return;
}

$assignment = Assignment::latest()->first();
$practice = DiscordPractice::where('status', 'scheduled')->first();
$package = PracticePackage::where('status', 'published')->first();

$totals = [
    'submissions' => 0,
    'reservations' => 0,
    'orders' => 0,
];

foreach ($students as $student) {
    Auth::login($student);

    if ($assignment) {
        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $student->id],
            [
                'body' => 'Entrega QA generada por student_flow.',
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );
        logStudent("{$student->email}: Submission {$submission->id} registrado.");
        $totals['submissions']++;
    }

    if ($practice) {
        $student->discordPracticeReservations()->updateOrCreate(
            ['discord_practice_id' => $practice->id],
            ['status' => 'reserved', 'reserved_at' => now()]
        );
        logStudent("{$student->email}: Reserva confirmada para {$practice->title}.");
        $totals['reservations']++;
    }

    if ($package) {
        PracticePackageOrder::updateOrCreate(
            ['practice_package_id' => $package->id, 'user_id' => $student->id],
            ['status' => 'paid', 'sessions_remaining' => $package->sessions_count, 'payment_reference' => 'QA-ORDER-'.Str::upper(Str::random(4))]
        );
        logStudent("{$student->email}: Orden creada para {$package->title}.");
        $totals['orders']++;
    }

    $student->update([
        'experience_points' => $student->experience_points + 50,
        'current_streak' => ($student->current_streak ?? 0) + 1,
    ]);
}

$summary = json_encode($totals);

logStudent('Resumen Student: '.$summary);
logStudent('Flujo Student finalizado.');

