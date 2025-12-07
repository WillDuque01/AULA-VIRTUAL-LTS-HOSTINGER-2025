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

function logTeacher(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$teachers = User::role('teacher')->limit(5)->get();

if ($teachers->isEmpty()) {
    logTeacher('No hay docentes disponibles.');

    return;
}

$course = Course::first();

if (! $course) {
    logTeacher('No hay curso para proponer contenido.');

    return;
}

foreach ($teachers as $teacher) {
    Auth::login($teacher);

    $chapter = Chapter::firstOrCreate(
        ['course_id' => $course->id, 'title' => 'Propuestas QA'],
        ['position' => 99, 'status' => 'pending', 'created_by' => $teacher->id]
    );

    $lesson = Lesson::create([
        'chapter_id' => $chapter->id,
        'type' => 'text',
        'config' => [
            'title' => 'Lección Teacher QA '.Str::upper(Str::random(3)),
            'body' => '<p>Contenido generado por teacher_flow.</p>',
            'estimated_minutes' => 12,
        ],
        'position' => ($chapter->lessons()->max('position') ?? 0) + 1,
        'locked' => true,
        'status' => 'pending',
        'created_by' => $teacher->id,
    ]);

    logTeacher(sprintf(
        'Lección propuesta por %s: %s (ID %d).',
        $teacher->email,
        $lesson->config['title'],
        $lesson->id
    ));

    $submission = TeacherSubmission::create([
        'user_id' => $teacher->id,
        'course_id' => $course->id,
        'chapter_id' => $chapter->id,
        'type' => 'lesson',
        'title' => $lesson->config['title'],
        'summary' => 'Se solicita revisión y desbloqueo.',
        'payload' => ['lesson_id' => $lesson->id],
        'status' => 'pending',
    ]);

    logTeacher("Submission creada: {$submission->id}.");

    $lesson->update(['locked' => false, 'status' => 'published']);
    logTeacher("Lección {$lesson->id} desbloqueada.");

    $profileMeta = $teacher->profile_meta ?? [];
    $profileMeta['missions_completed'][] = 'teacher_flow';
    $teacher->update([
        'headline' => 'Docente QA Automations',
        'profile_meta' => $profileMeta,
        'profile_completion_score' => 100,
    ]);

    logTeacher("Perfil docente actualizado para {$teacher->email}.");
}

$stats = [
    'pending_submissions' => TeacherSubmission::where('status', 'pending')->count(),
    'lessons_created' => Lesson::whereIn('created_by', $teachers->pluck('id'))->count(),
];

logTeacher('Resumen Teacher: '.json_encode($stats));
logTeacher('Flujo Teacher finalizado.');

