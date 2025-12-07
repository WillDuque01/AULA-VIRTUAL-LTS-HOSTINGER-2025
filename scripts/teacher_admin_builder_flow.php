<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\TeacherSubmission;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logTeacherAdminBuilder(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$teacherAdmin = User::where('email', 'teacher.admin.qa@letstalkspanish.io')->first();

if (! $teacherAdmin || ! $teacherAdmin->hasRole('teacher_admin')) {
    logTeacherAdminBuilder('Teacher Admin QA no disponible o sin rol.');

    return;
}

Auth::login($teacherAdmin);

$course = Course::where('slug', 'espanol-a1')->first() ?? Course::first();

if (! $course) {
    logTeacherAdminBuilder('No hay cursos publicados para operar el builder.');

    return;
}

logTeacherAdminBuilder(sprintf('Iniciando builder como %s sobre curso %s (%d).', $teacherAdmin->email, $course->slug ?? 'sin-slug', $course->id));

$chapter = Chapter::create([
    'course_id' => $course->id,
    'title' => 'Teacher Admin Chapter '.Str::upper(Str::random(4)),
    'position' => ($course->chapters()->max('position') ?? 0) + 1,
    'status' => 'pending',
    'created_by' => $teacherAdmin->id,
]);

logTeacherAdminBuilder("Capítulo creado en estado {$chapter->status} (ID {$chapter->id}).");

$lessonDefinitions = [
    [
        'type' => 'text',
        'title' => 'Docente Admin Texto',
        'body' => '<p>Primer borrador generado por teacher_admin_builder_flow.</p>',
        'estimated_minutes' => 8,
        'status' => 'draft',
    ],
    [
        'type' => 'video',
        'title' => 'Docente Admin Video',
        'body' => '<p>Descripción del video QA.</p>',
        'estimated_minutes' => 6,
        'status' => 'pending',
        'video_id' => 'xvFZjo5PgG0',
    ],
];

$lessons = [];

foreach ($lessonDefinitions as $index => $definition) {
    $config = [
        'title' => $definition['title'].' '.Str::upper(Str::random(3)),
        'body' => $definition['body'],
        'estimated_minutes' => $definition['estimated_minutes'],
        'blocks' => [
            [
                'type' => 'card',
                'title' => 'Card QA',
                'content' => 'Validando tarjetas creadas por Teacher Admin.',
            ],
            [
                'type' => 'section',
                'title' => 'Sección QA',
                'content' => 'Probando secciones y texto enriquecido.',
            ],
        ],
    ];

    if ($definition['type'] === 'video') {
        $config['source'] = 'youtube';
        $config['video_id'] = $definition['video_id'];
    }

    $lesson = Lesson::create([
        'chapter_id' => $chapter->id,
        'type' => $definition['type'],
        'config' => $config,
        'position' => $index + 1,
        'locked' => $definition['status'] !== 'published',
        'status' => $definition['status'],
        'created_by' => $teacherAdmin->id,
    ]);

    $lessons[] = $lesson;
    logTeacherAdminBuilder("Lección {$lesson->config['title']} creada (ID {$lesson->id}, estado {$lesson->status}).");
}

$lessonToApprove = $lessons[0];
$updatedConfig = $lessonToApprove->config;
$updatedConfig['blocks'][] = [
    'type' => 'card',
    'title' => 'Card adicional',
    'content' => 'Agregada para validar edición posterior.',
];
$updatedConfig['blocks'][] = [
    'type' => 'section',
    'title' => 'Notas Teacher Admin',
    'content' => 'Confirmando edición de secciones.',
];
$updatedConfig['body'] .= '<p>Actualizado para QA.</p>';

$lessonToApprove->update([
    'config' => $updatedConfig,
    'status' => 'published',
    'locked' => false,
]);

logTeacherAdminBuilder("Lección {$lessonToApprove->id} actualizada y publicada (bloques: ".count($updatedConfig['blocks']).').');

$lessonToRemove = $lessons[1];
$lessonToRemove->delete();

logTeacherAdminBuilder("Lección {$lessonToRemove->id} eliminada para validar el borrado.");

$chapter->lessons()
    ->orderBy('position')
    ->get()
    ->each(function (Lesson $lesson, int $index): void {
        $lesson->update(['position' => $index + 1]);
    });

logTeacherAdminBuilder('Posiciones reindexadas tras el borrado.');

$submission = TeacherSubmission::create([
    'user_id' => $teacherAdmin->id,
    'course_id' => $course->id,
    'chapter_id' => $chapter->id,
    'type' => 'lesson',
    'title' => 'Teacher Admin Builder QA',
    'summary' => 'Solicita revisión del capítulo y la lección publicada.',
    'payload' => [
        'chapter_id' => $chapter->id,
        'lesson_id' => $lessonToApprove->id,
    ],
    'status' => 'pending',
]);

logTeacherAdminBuilder("Submission creada para revisión QA (ID {$submission->id}).");

$stats = [
    'chapters_by_teacher_admin' => Chapter::where('created_by', $teacherAdmin->id)->count(),
    'lessons_by_teacher_admin' => Lesson::where('created_by', $teacherAdmin->id)->count(),
    'pending_submissions' => TeacherSubmission::where('user_id', $teacherAdmin->id)->where('status', 'pending')->count(),
];

logTeacherAdminBuilder('Resumen builder Teacher Admin: '.json_encode($stats));
logTeacherAdminBuilder('Flujo teacher_admin_builder_flow finalizado.');


