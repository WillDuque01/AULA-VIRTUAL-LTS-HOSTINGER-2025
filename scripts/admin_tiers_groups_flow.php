<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logTiers(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logTiers('Admin QA no disponible para tiers/grupos.');

    return;
}

Auth::login($admin);

$course = Course::where('slug', 'qa-spanish-lab')->first();

$tier = Tier::create([
    'name' => 'QA Premium '.Str::upper(Str::random(2)),
    'slug' => 'qa-premium-'.Str::lower(Str::random(4)),
    'tagline' => 'Acceso preferencial para pruebas',
    'description' => 'Tier generado por admin_tiers_groups_flow.',
    'priority' => 20,
    'access_type' => 'vip',
    'is_default' => false,
    'is_active' => true,
    'price_monthly' => 129,
    'currency' => 'USD',
    'features' => [
        'Planner prioritario',
        'Acceso a QA bootcamps',
        'Canales exclusivos',
    ],
    'metadata' => ['origin' => 'admin_tiers_groups_flow'],
]);

logTiers("Tier creado: {$tier->name} (ID {$tier->id}).");

if ($course) {
    $tier->courses()->syncWithoutDetaching([$course->id]);
    logTiers("Curso {$course->slug} enlazado al tier.");
}

$students = User::role('student_vip')->limit(2)->get();

foreach ($students as $student) {
    $tier->users()->syncWithoutDetaching([
        $student->id => [
            'status' => 'active',
            'source' => 'admin_tiers_groups_flow',
            'assigned_by' => $admin->id,
            'starts_at' => now(),
            'metadata' => json_encode(['note' => 'Asignado para QA']),
        ],
    ]);
    logTiers("Usuario {$student->email} asignado al tier {$tier->name}.");
}

$group = StudentGroup::create([
    'name' => 'Squad QA '.Str::upper(Str::random(3)),
    'slug' => 'squad-qa-'.Str::lower(Str::random(3)),
    'tier_id' => $tier->id,
    'description' => 'Grupo creado por admin_tiers_groups_flow.',
    'capacity' => 5,
    'starts_at' => now(),
    'is_active' => true,
    'metadata' => ['channel' => '#qa-squad'],
]);

logTiers("Grupo creado: {$group->name} (ID {$group->id}).");

foreach ($students as $student) {
    $group->students()->syncWithoutDetaching([
        $student->id => [
            'assigned_by' => $admin->id,
            'joined_at' => now(),
            'metadata' => json_encode(['enrolled_via' => 'tier-sync']),
        ],
    ]);
    logTiers("Usuario {$student->email} agregado al grupo {$group->name}.");
}

$summary = [
    'tier_users' => $tier->users()->count(),
    'group_members' => $group->students()->count(),
];

logTiers('Resumen tier/grupo: '.json_encode($summary));
logTiers('Flujo Admin Tiers & Groups finalizado.');

