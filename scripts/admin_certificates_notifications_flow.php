<?php

declare(strict_types=1);

use App\Events\CertificateIssued;
use App\Events\LessonCompleted;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionExpiring;
use App\Events\TierUpdated;
use App\Models\Certificate;
use App\Models\GamificationEvent;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Support\Certificates\CertificateGenerator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logCertificatesFlow(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

logCertificatesFlow('Iniciando admin_certificates_notifications_flow.');

$student = User::role(['student_paid', 'student_vip', 'student_free'])->inRandomOrder()->first();
$lesson = Lesson::with('chapter.course')->published()->latest('id')->first();

if (! $student || ! $lesson || ! $lesson->chapter?->course) {
    logCertificatesFlow('Faltan datos base (student/lesson/course) para ejecutar el flujo.');

    return;
}

$course = $lesson->chapter->course;
logCertificatesFlow(sprintf('Usando curso %s (%d) y lesson %d.', $course->slug ?? 'sin-slug', $course->id, $lesson->id));

try {
    /** @var CertificateGenerator $generator */
    $generator = app(CertificateGenerator::class);
    $certificate = $generator->generate($student, $course, [
        'percent' => 100,
        'source' => 'admin_certificates_notifications_flow',
    ]);
    logCertificatesFlow(sprintf('Certificado generado (%s). Archivo: %s', $certificate->code, $certificate->file_path));
} catch (Throwable $exception) {
    $code = strtoupper(Str::random(10));
    $certificate = Certificate::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'code' => $code,
        'file_path' => sprintf('certificates/manual-%s.pdf', $code),
        'issued_at' => now(),
        'metadata' => [
            'percent' => 100,
            'source' => 'admin_certificates_notifications_flow-fallback',
            'generator_error' => $exception->getMessage(),
        ],
    ]);

    CertificateIssued::dispatch($certificate);
    logCertificatesFlow(sprintf('Certificado creado manualmente (%s) debido a error: %s', $code, $exception->getMessage()));
}

$points = 120;
$streak = random_int(3, 12);

GamificationEvent::create([
    'user_id' => $student->id,
    'lesson_id' => $lesson->id,
    'type' => 'lesson_completed',
    'points' => $points,
    'metadata' => [
        'badge' => 'qa_full_run',
        'source' => 'admin_certificates_notifications_flow',
    ],
]);

LessonCompleted::dispatch($student, $lesson, $points, $streak);
logCertificatesFlow(sprintf('Evento LessonCompleted disparado (points=%d, streak=%d).', $points, $streak));

$tier = $student->tiers()->first() ?? Tier::first();

if ($tier) {
    $recipients = $tier->activeUsers()->take(5)->get();

    if ($recipients->isEmpty()) {
        $recipients = collect([$student])->filter();
    }

    TierUpdated::dispatch($tier, $recipients);
    logCertificatesFlow(sprintf('TierUpdated enviado para tier %s (%d) a %d destinatarios.', $tier->name, $tier->id, $recipients->count()));

    $subscription = Subscription::firstOrCreate(
        [
            'user_id' => $student->id,
            'tier_id' => $tier->id,
            'provider' => 'qa-cert-flow',
        ],
        [
            'status' => 'active',
            'starts_at' => now()->subDays(15),
            'renews_at' => now()->addDays(3),
            'amount' => $tier->price_monthly ?? 59,
            'currency' => $tier->currency ?? 'USD',
            'metadata' => [
                'origin' => 'admin_certificates_notifications_flow',
            ],
        ]
    );

    $subscription->forceFill([
        'status' => 'active',
        'renews_at' => now()->addDays(3),
        'cancelled_at' => null,
    ])->save();

    $subscription->refresh();
    SubscriptionExpiring::dispatch($subscription);
    logCertificatesFlow('SubscriptionExpiring enviado.');

    $subscription->forceFill([
        'status' => 'expired',
        'cancelled_at' => now(),
    ])->save();

    $subscription->refresh();
    SubscriptionExpired::dispatch($subscription);
    logCertificatesFlow('SubscriptionExpired enviado.');
} else {
    logCertificatesFlow('No se encontró tier para disparar TierUpdated / Subscription notifications.');
}

logCertificatesFlow('Flujo admin_certificates_notifications_flow finalizado.');


