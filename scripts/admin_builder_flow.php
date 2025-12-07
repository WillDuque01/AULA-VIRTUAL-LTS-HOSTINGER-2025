<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logLine(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logLine('Admin QA user not found or missing role.');

    return;
}

Auth::login($admin);

$course = Course::where('slug', 'qa-spanish-lab')->first();

if (! $course) {
    logLine('Course qa-spanish-lab not found.');

    return;
}

logLine("Iniciando flujo Admin Builder sobre curso {$course->slug}.");

$chapter = Chapter::create([
    'course_id' => $course->id,
    'title' => 'Módulo Admin Flow '.Str::upper(Str::random(4)),
    'position' => ($course->chapters()->max('position') ?? 0) + 1,
    'status' => 'published',
    'created_by' => $admin->id,
]);

logLine("Capítulo creado: {$chapter->title} (ID {$chapter->id}).");

$lessonsData = [
    [
        'type' => 'video',
        'title' => 'Video onboarding builder',
        'body' => 'Video para validar tarjetas y secciones',
        'video_id' => 'xvFZjo5PgG0',
        'estimated_minutes' => 9,
    ],
    [
        'type' => 'text',
        'title' => 'Guía editor enriquecido',
        'body' => '<p>Contenido inicial para probar cards/sections.</p>',
        'estimated_minutes' => 7,
    ],
];

$createdLessons = [];

foreach ($lessonsData as $index => $data) {
    $config = [
        'title' => $data['title'],
        'body' => $data['body'],
        'estimated_minutes' => $data['estimated_minutes'],
        'blocks' => [
            [
                'type' => 'card',
                'title' => 'Card inicial',
                'content' => 'Creada automáticamente por admin_builder_flow.',
            ],
            [
                'type' => 'section',
                'title' => 'Sección QA',
                'content' => 'Simulando edición de secciones.',
            ],
        ],
    ];

    if ($data['type'] === 'video') {
        $config['source'] = 'youtube';
        $config['video_id'] = $data['video_id'];
    }

    $lesson = Lesson::create([
        'chapter_id' => $chapter->id,
        'type' => $data['type'],
        'config' => $config,
        'position' => $index + 1,
        'locked' => false,
        'status' => 'published',
        'created_by' => $admin->id,
    ]);

    $createdLessons[] = $lesson;
    logLine("Lección creada: {$lesson->config['title']} (ID {$lesson->id}).");
}

$lessonToEdit = $createdLessons[0];
$config = $lessonToEdit->config;
$config['blocks'][] = [
    'type' => 'card',
    'title' => 'Card añadida via script',
    'content' => 'Validando edición posterior en builder.',
];
$config['blocks'][] = [
    'type' => 'section',
    'title' => 'Resumen de cambios',
    'content' => 'Esta sección emula el texto enriquecido.',
];
$config['body'] .= '<p>Actualizado por flujo admin.</p>';

$lessonToEdit->update(['config' => $config]);
logLine("Lección actualizada: {$lessonToEdit->config['title']} (bloques: ".count($config['blocks']).').');

$lessonToDelete = $createdLessons[1];
$lessonToDelete->delete();
logLine("Lección eliminada para validar borrado: {$lessonToDelete->id}.");

// Reordenar posiciones restantes
$remainingLessons = $chapter->lessons()->orderBy('position')->get();
foreach ($remainingLessons as $pos => $lesson) {
    $lesson->update(['position' => $pos + 1]);
}
logLine('Posiciones reindexadas tras la eliminación.');

logLine('Flujo Admin Builder finalizado correctamente.');

