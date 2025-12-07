<?php

declare(strict_types=1);

use App\Models\CohortRegistration;
use App\Models\CohortTemplate;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logCohorts(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logCohorts('Admin QA no disponible para cohorts.');

    return;
}

Auth::login($admin);

$package = PracticePackage::first();

$template = CohortTemplate::create([
    'name' => 'Bootcamp QA '.Str::upper(Str::random(3)),
    'slug' => 'bootcamp-qa-'.Str::lower(Str::random(4)),
    'description' => 'Template generado por admin_cohorts_flow.',
    'type' => 'bootcamp',
    'cohort_label' => 'QA Bootcamp',
    'duration_minutes' => 90,
    'capacity' => 8,
    'price_amount' => 149,
    'price_currency' => 'USD',
    'status' => 'published',
    'is_featured' => true,
    'requires_package' => false,
    'practice_package_id' => $package?->id,
    'slots' => [
        [
            'day' => 'Tuesday',
            'time' => '18:00',
            'timezone' => 'America/Bogota',
        ],
        [
            'day' => 'Thursday',
            'time' => '18:00',
            'timezone' => 'America/Bogota',
        ],
    ],
    'meta' => [
        'level' => 'B2',
        'focus' => 'Speaking + Grammar',
    ],
    'created_by' => $admin->id,
]);

logCohorts("Template creado: {$template->name} (ID {$template->id}).");

$students = User::role('student_paid')->limit(2)->get();

foreach ($students as $student) {
    $registration = CohortRegistration::updateOrCreate(
        [
            'cohort_template_id' => $template->id,
            'user_id' => $student->id,
        ],
        [
            'status' => 'paid',
            'payment_reference' => 'BOOTCAMP-'.Str::upper(Str::random(6)),
            'amount' => 149,
            'currency' => 'USD',
            'meta' => ['origin' => 'admin_cohorts_flow'],
        ]
    );

    logCohorts("Registro creado para {$student->email} (reg #{$registration->id}).");
}

$template->refreshEnrollmentMetrics();
logCohorts("Enrolled count actualizado: {$template->enrolled_count} / {$template->capacity}.");

$template->update([
    'description' => $template->description.' Actualizado con beneficios extra.',
    'status' => 'archived',
]);

logCohorts('Template archivado para validar cambios de estado.');

$stats = CohortTemplate::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
logCohorts('Resumen de templates: '.json_encode($stats));

logCohorts('Flujo Admin Cohorts finalizado.');

