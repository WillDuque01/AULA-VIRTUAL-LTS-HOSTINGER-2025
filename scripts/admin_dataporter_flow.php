<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\User;
use App\Support\DataPorter\DataPorter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logDataPorter(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

/**
 * @param  \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse  $response
 */
function captureStreamedResponse(Response|StreamedResponse $response): string
{
    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logDataPorter('Admin QA no disponible para DataPorter.');

    return;
}

Auth::login($admin);

/** @var DataPorter $porter */
$porter = app(DataPorter::class);

Storage::disk('local')->makeDirectory('qa_exports');

$exports = [
    [
        'dataset' => 'video_player_events',
        'format' => 'csv',
        'filters' => [
            'date_from' => now()->subDays(14)->toDateTimeString(),
        ],
    ],
    [
        'dataset' => 'practice_package_orders',
        'format' => 'json',
        'filters' => [],
    ],
];

foreach ($exports as $export) {
    $response = $porter->stream(
        $export['dataset'],
        $export['format'],
        $export['filters'],
        $admin
    );

    $relativeFilename = sprintf(
        'qa_exports/%s_%s.%s',
        $export['dataset'],
        now()->format('Ymd_His'),
        $export['format']
    );

    $payload = captureStreamedResponse($response);
    $absolutePath = storage_path('app/'.$relativeFilename);
    if (! is_dir(dirname($absolutePath))) {
        mkdir(dirname($absolutePath), 0775, true);
    }
    file_put_contents($absolutePath, $payload);

    logDataPorter("Export {$export['dataset']} generado en storage/app/{$relativeFilename} (".strlen($payload)." bytes).");
}

$student = User::role('student_paid')->first();

if ($student) {
    $importRows = [
        [
            'user_id' => $student->id,
            'course_id' => optional(Course::where('slug', 'qa-spanish-lab')->first())->id,
            'lesson_id' => null,
            'practice_package_id' => null,
            'category' => 'qa_import',
            'scope' => 'checkout',
            'value' => 1,
            'payload' => json_encode(['source' => 'admin_dataporter_flow']),
            'captured_at' => now()->toDateTimeString(),
        ],
    ];

    $importPath = storage_path('app/qa_exports/student_snapshots.json');
    if (! is_dir(dirname($importPath))) {
        mkdir(dirname($importPath), 0775, true);
    }
    file_put_contents($importPath, json_encode($importRows, JSON_PRETTY_PRINT));

    $imported = $porter->import('student_activity_snapshots', $importPath, $admin);
    logDataPorter("Import student_activity_snapshots completado ({$imported} filas).");
} else {
    logDataPorter('No se encontró estudiante para probar importación.');
}

$teacherAdmin = User::where('email', 'teacher.admin.qa@letstalkspanish.io')->first();

if ($teacherAdmin) {
    try {
        $porter->sanitizeFilters('video_player_events', [], $teacherAdmin);
    } catch (AuthorizationException $exception) {
        logDataPorter('Restricción teacher_admin sin filtros confirmada: '.$exception->getMessage());
    }

    $course = Course::where('slug', 'qa-spanish-lab')->first();

    if ($course) {
        $scopedFilters = ['course_id' => $course->id];
        $porter->sanitizeFilters('video_player_events', $scopedFilters, $teacherAdmin);
        logDataPorter('Teacher Admin con filtros pudo sanear filtros correctamente (course_id scope).');
    }
}

logDataPorter('Flujo DataPorter finalizado.');

