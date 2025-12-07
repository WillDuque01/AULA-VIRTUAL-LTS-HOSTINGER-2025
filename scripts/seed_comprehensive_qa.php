<?php

declare(strict_types=1);

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseI18n;
use App\Models\CourseTeacher;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function summarize(string $label, mixed ...$args): void
{
    $message = $args ? vsprintf($label, $args) : $label;
    echo sprintf("[%s] %s", now()->toDateTimeString(), $message).PHP_EOL;
}

function ensureUser(array $definition): User
{
    $profileDefaults = [
        'name' => $definition['name'],
        'first_name' => $definition['first_name'] ?? $definition['name'],
        'last_name' => $definition['last_name'] ?? 'QA',
        'phone' => $definition['phone'] ?? '+1'.mt_rand(3000000000, 3999999999),
        'country' => $definition['country'] ?? 'Colombia',
        'state' => $definition['state'] ?? 'Bogotá',
        'city' => $definition['city'] ?? 'Bogotá',
        'headline' => $definition['headline'] ?? 'Cuenta QA automatizada',
        'bio' => $definition['bio'] ?? 'Perfil creado para validar flujos end-to-end.',
        'experience_points' => $definition['experience_points'] ?? 1200,
        'current_streak' => $definition['current_streak'] ?? 3,
        'profile_completion_score' => 100,
        'profile_completed_at' => now(),
        'profile_meta' => [
            'availability' => 'QA coverage',
            'timezone' => 'America/Bogota',
        ],
        'email_verified_at' => now(),
    ];

    $user = User::updateOrCreate(
        ['email' => $definition['email']],
        array_merge(
            $profileDefaults,
            [
                'password' => Hash::make($definition['password']),
            ]
        )
    );

    $user->syncRoles($definition['roles']);

    return $user;
}

function ensureAssignment(Lesson $lesson, array $payload): Assignment
{
    return Assignment::updateOrCreate(
        ['lesson_id' => $lesson->id],
        [
            'instructions' => $payload['instructions'],
            'due_at' => $payload['due_at'],
            'max_points' => $payload['max_points'] ?? 100,
            'passing_score' => $payload['passing_score'] ?? 70,
            'requires_approval' => $payload['requires_approval'] ?? true,
            'rubric' => $payload['rubric'] ?? [
                ['label' => 'Contenido', 'points' => 50],
                ['label' => 'Fluidez', 'points' => 30],
                ['label' => 'Creatividad', 'points' => 20],
            ],
        ]
    );
}

function ensureAssignmentSubmission(Assignment $assignment, User $student, array $payload): AssignmentSubmission
{
    return AssignmentSubmission::updateOrCreate(
        [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ],
        [
            'body' => $payload['body'],
            'status' => $payload['status'] ?? 'submitted',
            'score' => $payload['score'] ?? null,
            'max_points' => $payload['max_points'] ?? $assignment->max_points,
            'submitted_at' => $payload['submitted_at'] ?? now(),
            'graded_at' => $payload['graded_at'] ?? null,
            'feedback' => $payload['feedback'] ?? null,
        ]
    );
}

DB::transaction(function (): void {
    // === 1. Usuarios base ===
    $primaryAdmin = ensureUser([
        'email' => 'admin.qa@letstalkspanish.io',
        'name' => 'QA Platform Admin',
        'first_name' => 'Alicia',
        'last_name' => 'Admin',
        'password' => 'AdminQA2025!',
        'roles' => ['Admin'],
    ]);

    $secondaryTeacherAdmin = ensureUser([
        'email' => 'teacher.admin.qa@letstalkspanish.io',
        'name' => 'QA Academic Lead',
        'first_name' => 'Carlos',
        'last_name' => 'Lead',
        'password' => 'TeacherAdminQA2025!',
        'roles' => ['teacher_admin'],
    ]);

    $teacherPool = [];
    for ($i = 1; $i <= 5; $i++) {
        $teacherPool[] = ensureUser([
            'email' => sprintf('teacher.qa%02d@letstalkspanish.io', $i),
            'name' => sprintf('Teacher QA %02d', $i),
            'first_name' => 'Teacher',
            'last_name' => sprintf('QA%02d', $i),
            'password' => sprintf('TeacherQA2025!%02d', $i),
            'roles' => ['teacher'],
            'headline' => 'Facilitador QA',
            'bio' => 'Docente QA para validar propuestas y planner.',
        ]);
    }

    $studentRoles = ['student_paid', 'student_vip', 'student_free'];
    $studentPool = [];
    for ($i = 1; $i <= 18; $i++) {
        $role = $studentRoles[($i - 1) % count($studentRoles)];
        $studentPool[] = ensureUser([
            'email' => sprintf('student.qa%02d@letstalkspanish.io', $i),
            'name' => sprintf('Student QA %02d', $i),
            'first_name' => 'Student',
            'last_name' => sprintf('QA%02d', $i),
            'password' => sprintf('StudentQA2025!%02d', $i),
            'roles' => [$role],
            'headline' => strtoupper($role).' tester',
            'bio' => 'Estudiante QA para flujos completos.',
            'experience_points' => 800 + ($i * 10),
            'current_streak' => ($i % 5) + 1,
        ]);
    }

    summarize(
        'Usuarios QA actualizados: Admins/TeacherAdmins/Teachers/Students => %s/%s/%s/%s',
        User::role('Admin')->count(),
        User::role('teacher_admin')->count(),
        User::role('teacher')->count(),
        User::role('student_paid')->count() + User::role('student_vip')->count() + User::role('student_free')->count()
    );

    // === 2. Curso QA dedicado ===
    $course = Course::updateOrCreate(
        ['slug' => 'qa-spanish-lab'],
        [
            'level' => 'B2',
            'published' => true,
        ]
    );

    CourseI18n::updateOrCreate(
        ['course_id' => $course->id, 'locale' => 'es'],
        [
            'title' => 'QA Spanish Lab',
            'description' => 'Curso dedicado a flujos de prueba: módulos, builder, player y planner.',
        ]
    );
    CourseI18n::updateOrCreate(
        ['course_id' => $course->id, 'locale' => 'en'],
        [
            'title' => 'QA Spanish Lab',
            'description' => 'Testing playground for end-to-end LMS scenarios.',
        ]
    );

    $moduleDefinitions = [
        [
            'title' => 'Laboratorio de Conversación QA',
            'lessons' => [
                [
                    'type' => 'video',
                    'title' => 'Dinámicas de conversación',
                    'body' => 'Video introductorio con mejores prácticas de conversación.',
                    'video_id' => 'dQw4w9WgXcQ',
                    'estimated_minutes' => 12,
                    'author' => $teacherPool[0],
                ],
                [
                    'type' => 'text',
                    'title' => 'Guía de tarjetas QA',
                    'body' => '<p>Incluye cards, bullets y secciones para validar el editor.</p>',
                    'estimated_minutes' => 8,
                    'author' => $teacherPool[1],
                ],
                [
                    'type' => 'pdf',
                    'title' => 'Checklist de retroalimentación',
                    'body' => 'pdf://checklist-qa.pdf',
                    'estimated_minutes' => 5,
                    'author' => $teacherPool[2],
                ],
            ],
        ],
        [
            'title' => 'Laboratorio de Gramática QA',
            'lessons' => [
                [
                    'type' => 'video',
                    'title' => 'Estructuras condicionales',
                    'body' => 'Video de gramática avanzada con ejemplos.',
                    'video_id' => 'V-_O7nl0Ii0',
                    'estimated_minutes' => 15,
                    'author' => $teacherPool[3],
                ],
                [
                    'type' => 'text',
                    'title' => 'Retos interactivos',
                    'body' => '<p>Sección con retos que usan cards y bloques.</p>',
                    'estimated_minutes' => 10,
                    'author' => $teacherPool[4],
                ],
            ],
        ],
    ];

    $lessonReferences = [];
    foreach ($moduleDefinitions as $moduleIndex => $module) {
        $chapter = Chapter::updateOrCreate(
            ['course_id' => $course->id, 'title' => $module['title']],
            [
                'position' => $moduleIndex + 1,
                'status' => 'published',
                'created_by' => $primaryAdmin->id,
            ]
        );

        foreach ($module['lessons'] as $lessonIndex => $lessonData) {
            $config = [
                'title' => $lessonData['title'],
                'body' => $lessonData['body'],
                'estimated_minutes' => $lessonData['estimated_minutes'],
                'qa_blocks' => [
                    ['type' => 'card', 'title' => 'Tip QA', 'content' => 'Verifica CTA y edición.'],
                    ['type' => 'section', 'title' => 'Notas', 'content' => 'Probar edición inline.'],
                ],
            ];

            if (($lessonData['type'] ?? '') === 'video') {
                $config['source'] = 'youtube';
                $config['video_id'] = $lessonData['video_id'] ?? 'dQw4w9WgXcQ';
            }

            $lesson = Lesson::updateOrCreate(
                [
                    'chapter_id' => $chapter->id,
                    'position' => $lessonIndex + 1,
                    'type' => $lessonData['type'],
                ],
                [
                    'config' => $config,
                    'locked' => false,
                    'status' => 'published',
                    'created_by' => $lessonData['author']->id ?? $primaryAdmin->id,
                ]
            );

            $lessonKey = Str::slug($chapter->title.'-'.$lessonData['title']);
            $lessonReferences[$lessonKey] = $lesson;
        }
    }

    $chapterCount = $course->chapters()->count();
    $lessonCount = $course->chapters()->withCount('lessons')->get()->sum('lessons_count');
    summarize(
        'Curso QA "%s" listo con %d capítulos y %d lecciones.',
        $course->slug,
        $chapterCount,
        $lessonCount
    );

    // === 3. Asignar docentes al curso QA ===
    foreach ($teacherPool as $teacher) {
        CourseTeacher::updateOrCreate(
            [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'assigned_by' => $primaryAdmin->id,
            ]
        );
    }

    // === 4. Paquetes de práctica + planner ===
    $packageDefinitions = [
        [
            'title' => 'Pack Conversación QA',
            'subtitle' => '4 sesiones enfocado en speaking.',
            'lesson_key' => 'laboratorio-de-conversacion-qa-dinamicas-de-conversacion',
            'sessions' => 4,
            'price' => 59,
            'creator' => $teacherPool[0],
        ],
        [
            'title' => 'Pack Gramática QA',
            'subtitle' => '6 sesiones de gramática avanzada.',
            'lesson_key' => 'laboratorio-de-gramatica-qa-estructuras-condicionales',
            'sessions' => 6,
            'price' => 79,
            'creator' => $teacherPool[3],
        ],
        [
            'title' => 'Pack Mixto QA',
            'subtitle' => 'Combo speaking + grammar.',
            'lesson_key' => 'laboratorio-de-gramatica-qa-retos-interactivos',
            'sessions' => 5,
            'price' => 69,
            'creator' => $teacherPool[4],
        ],
    ];

    $packages = [];
    foreach ($packageDefinitions as $definition) {
        $lesson = $lessonReferences[$definition['lesson_key']] ?? null;

        if (! $lesson) {
            continue;
        }

        $packages[] = PracticePackage::updateOrCreate(
            [
                'title' => $definition['title'],
                'creator_id' => $definition['creator']->id,
            ],
            [
                'lesson_id' => $lesson->id,
                'subtitle' => $definition['subtitle'],
                'description' => 'Generado por script QA para validar shop/cart/checkout.',
                'sessions_count' => $definition['sessions'],
                'price_amount' => $definition['price'],
                'price_currency' => 'USD',
                'is_global' => true,
                'visibility' => 'public',
                'delivery_platform' => 'discord',
                'delivery_url' => 'https://discord.gg/qa-lab',
                'status' => 'published',
                'meta' => [
                    'thumbnail_path' => '/images/qa-pack.png',
                    'compare_at_amount' => $definition['price'] + 20,
                    'is_featured' => true,
                ],
            ]
        );
    }

    // Discord practices asociadas
    $practiceIndex = 0;
    foreach ($packages as $package) {
        $practiceIndex++;
        DiscordPractice::updateOrCreate(
            [
                'title' => sprintf('QA Practice %02d', $practiceIndex),
                'lesson_id' => $package->lesson_id,
            ],
            [
                'type' => 'group',
                'description' => 'Sesión de práctica automatizada para pruebas.',
                'cohort_label' => 'QA Cohort',
                'practice_package_id' => $package->id,
                'start_at' => Carbon::now()->addDays($practiceIndex)->setTime(16, 0),
                'end_at' => Carbon::now()->addDays($practiceIndex)->setTime(17, 0),
                'duration_minutes' => 60,
                'capacity' => 12,
                'discord_channel_url' => 'https://discord.gg/qa-lab',
                'meeting_token' => Str::upper(Str::random(6)),
                'status' => 'scheduled',
                'created_by' => $package->creator_id,
                'requires_package' => true,
            ]
        );
    }

    // === 5. Pedidos de paquetes y carrito ===
    $orderingStudents = array_slice($studentPool, 0, 10);
    foreach ($orderingStudents as $index => $student) {
        $package = $packages[$index % max(count($packages), 1)];
        PracticePackageOrder::updateOrCreate(
            [
                'practice_package_id' => $package->id,
                'user_id' => $student->id,
            ],
            [
                'status' => 'paid',
                'sessions_remaining' => $package->sessions_count - ($index % 2),
                'payment_reference' => sprintf('QA-ORDER-%s', Str::upper(Str::random(6))),
                'paid_at' => now()->subDays($index + 1),
                'meta' => [
                    'source' => 'qa-seeder',
                    'notes' => 'Genero pedidos para pruebas de checkout y historial.',
                ],
            ]
        );
    }

    // === 6. Assignments + submissions ===
    $assignmentLessons = array_slice(array_values($lessonReferences), 0, 2);
    $assignments = [];

    foreach ($assignmentLessons as $lesson) {
        $assignments[] = ensureAssignment($lesson, [
            'instructions' => 'Redacta un texto QA con feedback automático.',
            'due_at' => now()->addDays(5),
        ]);
    }

    $submissionStudents = array_slice($studentPool, 0, 6);
    foreach ($assignments as $assignmentIndex => $assignment) {
        foreach ($submissionStudents as $studentIndex => $student) {
            ensureAssignmentSubmission($assignment, $student, [
                'body' => sprintf(
                    "Entrega QA #%d.%d - contenido generado para pruebas completas.",
                    $assignmentIndex + 1,
                    $studentIndex + 1
                ),
                'status' => $studentIndex % 2 === 0 ? 'submitted' : 'graded',
                'score' => $studentIndex % 2 === 0 ? null : 85,
                'graded_at' => $studentIndex % 2 === 0 ? null : now()->subDay(),
                'feedback' => $studentIndex % 2 === 0 ? null : 'Retroalimentación QA automática.',
            ]);
        }
    }

    summarize('Asignaciones generadas: %d (con %d submissions).', count($assignments), AssignmentSubmission::count());
});

summarize('Seed QA completado.');

