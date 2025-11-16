<?php

namespace App\Support\DataPorter;

use App\Models\CourseTeacher;
use App\Models\DiscordPractice;
use App\Models\PracticePackageOrder;
use App\Models\StudentActivitySnapshot;
use App\Models\TeacherActivitySnapshot;
use App\Models\TeacherSubmission;
use App\Models\User;
use App\Models\VideoPlayerEvent;
use App\Support\Analytics\TelemetryRecorder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataPorter
{
    private ?array $cachedDefinitions = null;

    public function __construct(
        private readonly TelemetryRecorder $recorder
    ) {
    }

    public function datasetsFor(User $user, string $intent = 'export'): array
    {
        return collect($this->definitions())
            ->filter(function (array $definition) use ($user, $intent) {
                if ($intent === 'import' && ! ($definition['importable'] ?? false)) {
                    return false;
                }

                return $this->definitionAllowed($definition, $user);
            })
            ->all();
    }

    public function sanitizeFilters(string $datasetKey, array $filters, User $user): array
    {
        $definition = $this->definition($datasetKey, $user);

        $clean = [];
        foreach ($definition['filters'] ?? [] as $filterKey => $filter) {
            $value = $filters[$filterKey] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (($filter['type'] ?? null) === 'date') {
                $clean[$filterKey] = Carbon::parse($value)->toDateTimeString();
            } elseif (($filter['type'] ?? null) === 'number') {
                $clean[$filterKey] = (int) $value;
            } else {
                $clean[$filterKey] = trim((string) $value);
            }
        }

        $this->assertTeacherScope($definition, $user, $clean);

        return $clean;
    }

    public function stream(string $datasetKey, string $format, array $filters, User $user): StreamedResponse
    {
        $definition = $this->definition($datasetKey, $user);
        $filtered = $this->sanitizeFilters($datasetKey, $filters, $user);

        $query = $this->buildQuery($definition);
        $this->applyFilters($query, $filtered, $definition);

        $filename = sprintf('%s_%s.%s', $datasetKey, now()->format('Ymd_His'), $format);

        $writer = $format === 'json'
            ? $this->jsonWriter($query, $definition)
            : $this->csvWriter($query, $definition);

        $contentType = $format === 'json' ? 'application/json' : 'text/csv';

        return response()->streamDownload($writer, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    public function import(string $datasetKey, string $path, User $user): int
    {
        $definition = $this->definition($datasetKey, $user, intent: 'import');

        if (! ($definition['importable'] ?? false)) {
            throw new AuthorizationException('Dataset no permite importaciones.');
        }

        $rows = $this->parseFile($path);
        $transform = $definition['import_transform'] ?? null;

        if (! is_callable($transform)) {
            throw new AuthorizationException('Dataset no tiene mapeo de importación.');
        }

        $created = 0;

        foreach ($rows as $row) {
            $payload = $transform($row);

            if (! $payload) {
                continue;
            }

            if ($datasetKey === 'student_activity_snapshots') {
                $this->recorder->recordStudentSnapshot(
                    $payload['user_id'],
                    $payload['category'],
                    $payload['attributes']
                );
                $created++;

                continue;
            }

            $this->recorder->recordTeacherSnapshot(
                $payload['teacher_id'],
                $payload['category'],
                $payload['attributes']
            );
            $created++;
        }

        return $created;
    }

    private function definitions(): array
    {
        if ($this->cachedDefinitions !== null) {
            return $this->cachedDefinitions;
        }

        $this->cachedDefinitions = [
            'video_player_events' => [
                'label' => 'Eventos del reproductor',
                'description' => 'Telemetría cruda del player (play, seek, progreso).',
                'model' => VideoPlayerEvent::class,
                'date_column' => 'recorded_at',
                'order_column' => 'recorded_at',
                'with' => ['user:id,name,email'],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'recorded_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'recorded_at', 'operator' => '<='],
                    'course_id' => ['label' => 'ID curso', 'type' => 'number', 'column' => 'course_id'],
                    'lesson_id' => ['label' => 'ID lección', 'type' => 'number', 'column' => 'lesson_id'],
                    'event' => ['label' => 'Evento', 'type' => 'text', 'column' => 'event'],
                    'provider' => ['label' => 'Proveedor', 'type' => 'text', 'column' => 'provider'],
                ],
                'columns' => [
                    'recorded_at' => fn (VideoPlayerEvent $event) => optional($event->recorded_at)->toIso8601String(),
                    'user_id' => 'user_id',
                    'user_email' => fn (VideoPlayerEvent $event) => $event->user?->email,
                    'course_id' => 'course_id',
                    'lesson_id' => 'lesson_id',
                    'event' => 'event',
                    'provider' => 'provider',
                    'playback_seconds' => 'playback_seconds',
                    'watched_seconds' => 'watched_seconds',
                    'video_duration' => 'video_duration',
                    'playback_rate' => 'playback_rate',
                    'context' => 'context_tag',
                    'metadata' => fn (VideoPlayerEvent $event) => $event->metadata ?? [],
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id', 'lesson_id'],
                'importable' => false,
            ],
            'student_activity_snapshots' => [
                'label' => 'Snapshots de estudiantes',
                'description' => 'Eventos agregados (progreso, reservas, packs) por estudiante.',
                'model' => StudentActivitySnapshot::class,
                'date_column' => 'captured_at',
                'order_column' => 'captured_at',
                'with' => ['user:id,name,email'],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'captured_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'captured_at', 'operator' => '<='],
                    'course_id' => ['label' => 'ID curso', 'type' => 'number', 'column' => 'course_id'],
                    'lesson_id' => ['label' => 'ID lección', 'type' => 'number', 'column' => 'lesson_id'],
                    'practice_package_id' => ['label' => 'ID pack', 'type' => 'number', 'column' => 'practice_package_id'],
                    'category' => ['label' => 'Categoría', 'type' => 'text', 'column' => 'category'],
                ],
                'columns' => [
                    'captured_at' => fn (StudentActivitySnapshot $snapshot) => optional($snapshot->captured_at)->toIso8601String(),
                    'user_id' => 'user_id',
                    'user_email' => fn (StudentActivitySnapshot $snapshot) => $snapshot->user?->email,
                    'course_id' => 'course_id',
                    'lesson_id' => 'lesson_id',
                    'practice_package_id' => 'practice_package_id',
                    'category' => 'category',
                    'scope' => 'scope',
                    'value' => 'value',
                    'payload' => fn (StudentActivitySnapshot $snapshot) => $snapshot->payload ?? [],
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id', 'lesson_id'],
                'importable' => true,
                'import_transform' => function (array $row): ?array {
                    $userId = (int) ($row['user_id'] ?? 0);

                    if ($userId <= 0) {
                        return null;
                    }

                    return [
                        'user_id' => $userId,
                        'category' => $row['category'] ?? 'custom',
                        'attributes' => [
                            'course_id' => $this->toNullableInt($row['course_id'] ?? null),
                            'lesson_id' => $this->toNullableInt($row['lesson_id'] ?? null),
                            'practice_package_id' => $this->toNullableInt($row['practice_package_id'] ?? null),
                            'scope' => $row['scope'] ?? null,
                            'value' => $this->toNullableInt($row['value'] ?? null),
                            'payload' => $this->decodePayload($row['payload'] ?? null),
                            'captured_at' => $this->parseDate($row['captured_at'] ?? null),
                        ],
                    ];
                },
            ],
            'teacher_activity_snapshots' => [
                'label' => 'Snapshots de Teacher Admin',
                'description' => 'Acciones registradas de profesores/admin (planner, packs).',
                'model' => TeacherActivitySnapshot::class,
                'date_column' => 'captured_at',
                'order_column' => 'captured_at',
                'with' => ['teacher:id,name,email'],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'captured_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'captured_at', 'operator' => '<='],
                    'course_id' => ['label' => 'ID curso', 'type' => 'number', 'column' => 'course_id'],
                    'lesson_id' => ['label' => 'ID lección', 'type' => 'number', 'column' => 'lesson_id'],
                    'category' => ['label' => 'Categoría', 'type' => 'text', 'column' => 'category'],
                ],
                'columns' => [
                    'captured_at' => fn (TeacherActivitySnapshot $snapshot) => optional($snapshot->captured_at)->toIso8601String(),
                    'teacher_id' => 'teacher_id',
                    'teacher_email' => fn (TeacherActivitySnapshot $snapshot) => $snapshot->teacher?->email,
                    'course_id' => 'course_id',
                    'lesson_id' => 'lesson_id',
                    'category' => 'category',
                    'scope' => 'scope',
                    'value' => 'value',
                    'payload' => fn (TeacherActivitySnapshot $snapshot) => $snapshot->payload ?? [],
                ],
                'teacher_allowed' => false,
                'importable' => true,
                'import_transform' => function (array $row): ?array {
                    $teacherId = (int) ($row['teacher_id'] ?? 0);

                    if ($teacherId <= 0) {
                        return null;
                    }

                    return [
                        'teacher_id' => $teacherId,
                        'category' => $row['category'] ?? 'custom',
                        'attributes' => [
                            'course_id' => $this->toNullableInt($row['course_id'] ?? null),
                            'lesson_id' => $this->toNullableInt($row['lesson_id'] ?? null),
                            'practice_package_id' => $this->toNullableInt($row['practice_package_id'] ?? null),
                            'scope' => $row['scope'] ?? null,
                            'value' => $this->toNullableInt($row['value'] ?? null),
                            'payload' => $this->decodePayload($row['payload'] ?? null),
                            'captured_at' => $this->parseDate($row['captured_at'] ?? null),
                        ],
                    ];
                },
            ],
            'discord_practices' => [
                'label' => 'Prácticas Discord',
                'description' => 'Sesiones planificadas, estado, reservas y asignaciones de packs.',
                'model' => DiscordPractice::class,
                'date_column' => 'start_at',
                'order_column' => 'start_at',
                'with' => ['lesson.chapter.course', 'package', 'creator'],
                'with_count' => ['reservations'],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'start_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'start_at', 'operator' => '<='],
                    'status' => ['label' => 'Estado', 'type' => 'text', 'column' => 'status'],
                    'type' => ['label' => 'Tipo', 'type' => 'text', 'column' => 'type'],
                    'lesson_id' => ['label' => 'ID lección', 'type' => 'number', 'column' => 'lesson_id'],
                    'course_id' => [
                        'label' => 'ID curso',
                        'type' => 'number',
                        'apply' => function (Builder $query, $value): void {
                            $query->whereHas('lesson.chapter.course', function (Builder $builder) use ($value): void {
                                $builder->where('courses.id', $value);
                            });
                        },
                    ],
                    'creator_id' => ['label' => 'ID creador', 'type' => 'number', 'column' => 'created_by'],
                ],
                'columns' => [
                    'practice_id' => 'id',
                    'course_id' => fn (DiscordPractice $practice) => $practice->lesson?->chapter?->course?->id,
                    'course_slug' => fn (DiscordPractice $practice) => $practice->lesson?->chapter?->course?->slug,
                    'lesson_id' => 'lesson_id',
                    'lesson_title' => fn (DiscordPractice $practice) => data_get($practice->lesson?->config, 'title'),
                    'type' => 'type',
                    'status' => 'status',
                    'start_at' => fn (DiscordPractice $practice) => optional($practice->start_at)->toIso8601String(),
                    'end_at' => fn (DiscordPractice $practice) => optional($practice->end_at)->toIso8601String(),
                    'capacity' => 'capacity',
                    'reservations' => fn (DiscordPractice $practice) => $practice->reservations_count ?? $practice->reservations()->count(),
                    'requires_package' => fn (DiscordPractice $practice) => $practice->requires_package,
                    'practice_package_id' => 'practice_package_id',
                    'package_title' => fn (DiscordPractice $practice) => $practice->package?->title,
                    'creator_id' => 'created_by',
                    'creator_email' => fn (DiscordPractice $practice) => $practice->creator?->email,
                    'cohort_label' => 'cohort_label',
                    'discord_channel_url' => 'discord_channel_url',
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id', 'lesson_id'],
                'importable' => false,
            ],
            'practice_package_orders' => [
                'label' => 'Pedidos de packs de práctica',
                'description' => 'Órdenes con estado, sesiones y trazabilidad de pago.',
                'model' => PracticePackageOrder::class,
                'date_column' => 'created_at',
                'order_column' => 'created_at',
                'with' => [
                    'user:id,name,email',
                    'package:id,title,lesson_id,creator_id,sessions_count,price_amount,price_currency',
                    'package.lesson.chapter.course',
                    'package.creator:id,name,email',
                ],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'created_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'created_at', 'operator' => '<='],
                    'status' => ['label' => 'Estado', 'type' => 'text', 'column' => 'status'],
                    'practice_package_id' => ['label' => 'ID pack', 'type' => 'number', 'column' => 'practice_package_id'],
                    'user_id' => ['label' => 'ID estudiante', 'type' => 'number', 'column' => 'user_id'],
                    'course_id' => [
                        'label' => 'ID curso',
                        'type' => 'number',
                        'apply' => function (Builder $query, $value): void {
                            $query->whereHas('package.lesson.chapter.course', function (Builder $builder) use ($value): void {
                                $builder->where('courses.id', $value);
                            });
                        },
                    ],
                    'lesson_id' => [
                        'label' => 'ID lección',
                        'type' => 'number',
                        'apply' => function (Builder $query, $value): void {
                            $query->whereHas('package', function (Builder $builder) use ($value): void {
                                $builder->where('lesson_id', $value);
                            });
                        },
                    ],
                ],
                'columns' => [
                    'order_id' => 'id',
                    'user_id' => 'user_id',
                    'user_email' => fn (PracticePackageOrder $order) => $order->user?->email,
                    'practice_package_id' => 'practice_package_id',
                    'package_title' => fn (PracticePackageOrder $order) => $order->package?->title,
                    'teacher_id' => fn (PracticePackageOrder $order) => $order->package?->creator_id,
                    'teacher_email' => fn (PracticePackageOrder $order) => $order->package?->creator?->email,
                    'course_id' => fn (PracticePackageOrder $order) => $order->package?->lesson?->chapter?->course?->id,
                    'course_slug' => fn (PracticePackageOrder $order) => $order->package?->lesson?->chapter?->course?->slug,
                    'lesson_id' => fn (PracticePackageOrder $order) => $order->package?->lesson_id,
                    'status' => 'status',
                    'sessions_remaining' => 'sessions_remaining',
                    'paid_at' => fn (PracticePackageOrder $order) => optional($order->paid_at)->toIso8601String(),
                    'payment_reference' => 'payment_reference',
                    'created_at' => fn (PracticePackageOrder $order) => optional($order->created_at)->toIso8601String(),
                    'updated_at' => fn (PracticePackageOrder $order) => optional($order->updated_at)->toIso8601String(),
                    'meta' => fn (PracticePackageOrder $order) => $order->meta ?? [],
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id', 'lesson_id'],
                'importable' => false,
            ],
            'teacher_submissions' => [
                'label' => 'Propuestas docentes',
                'description' => 'Historial de módulos, lecciones y packs enviados por los docentes.',
                'model' => TeacherSubmission::class,
                'date_column' => 'created_at',
                'order_column' => 'created_at',
                'with' => ['author:id,name,email', 'course:id,slug'],
                'filters' => [
                    'date_from' => ['label' => 'Desde', 'type' => 'date', 'column' => 'created_at', 'operator' => '>='],
                    'date_to' => ['label' => 'Hasta', 'type' => 'date', 'column' => 'created_at', 'operator' => '<='],
                    'status' => ['label' => 'Estado', 'type' => 'text', 'column' => 'status'],
                    'type' => ['label' => 'Tipo', 'type' => 'text', 'column' => 'type'],
                    'course_id' => ['label' => 'ID curso', 'type' => 'number', 'column' => 'course_id'],
                    'user_id' => ['label' => 'ID docente', 'type' => 'number', 'column' => 'user_id'],
                ],
                'columns' => [
                    'created_at' => fn (TeacherSubmission $submission) => optional($submission->created_at)->toIso8601String(),
                    'teacher_id' => 'user_id',
                    'teacher_email' => fn (TeacherSubmission $submission) => $submission->author?->email,
                    'course_id' => 'course_id',
                    'course_slug' => fn (TeacherSubmission $submission) => $submission->course?->slug,
                    'type' => 'type',
                    'status' => 'status',
                    'feedback' => 'feedback',
                    'result_type' => 'result_type',
                    'result_id' => 'result_id',
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id'],
                'importable' => false,
            ],
            'course_teacher_assignments' => [
                'label' => 'Asignaciones curso-docente',
                'description' => 'Listado del pivote course_teacher con trazabilidad de asignadores.',
                'model' => CourseTeacher::class,
                'date_column' => 'created_at',
                'order_column' => 'created_at',
                'with' => ['course:id,slug', 'teacher:id,name,email', 'assigner:id,name,email'],
                'filters' => [
                    'course_id' => ['label' => 'ID curso', 'type' => 'number', 'column' => 'course_id'],
                    'teacher_id' => ['label' => 'ID docente', 'type' => 'number', 'column' => 'teacher_id'],
                ],
                'columns' => [
                    'assigned_at' => fn (CourseTeacher $assignment) => optional($assignment->created_at)->toIso8601String(),
                    'course_id' => 'course_id',
                    'course_slug' => fn (CourseTeacher $assignment) => $assignment->course?->slug,
                    'teacher_id' => 'teacher_id',
                    'teacher_email' => fn (CourseTeacher $assignment) => $assignment->teacher?->email,
                    'assigned_by' => 'assigned_by',
                    'assigner_email' => fn (CourseTeacher $assignment) => $assignment->assigner?->email,
                ],
                'teacher_allowed' => true,
                'teacher_scope_fields' => ['course_id'],
                'importable' => false,
            ],
        ];

        return $this->cachedDefinitions;
    }

    private function definition(string $key, User $user, string $intent = 'export'): array
    {
        $definitions = $this->definitions();

        if (! isset($definitions[$key])) {
            throw new AuthorizationException('Dataset no disponible.');
        }

        $definition = $definitions[$key];

        if ($intent === 'import' && ! ($definition['importable'] ?? false)) {
            throw new AuthorizationException('Dataset no admite importación.');
        }

        if (! $this->definitionAllowed($definition, $user)) {
            throw new AuthorizationException('No tienes acceso a este dataset.');
        }

        return $definition;
    }

    private function definitionAllowed(array $definition, User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isTeacherAdmin($user)) {
            return (bool) ($definition['teacher_allowed'] ?? false);
        }

        return false;
    }

    private function buildQuery(array $definition): Builder
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = $definition['model'];

        $query = $model::query();

        if (! empty($definition['with'])) {
            $query->with($definition['with']);
        }

        if (! empty($definition['with_count'])) {
            $query->withCount($definition['with_count']);
        }

        $orderColumn = $definition['order_column'] ?? $definition['date_column'] ?? 'id';
        $orderDirection = $definition['order_direction'] ?? 'desc';

        return $query->orderBy($orderColumn, $orderDirection);
    }

    private function applyFilters(Builder $query, array $filters, array $definition): void
    {
        foreach ($filters as $key => $value) {
            $filter = $definition['filters'][$key] ?? null;

            if (! $filter) {
                continue;
            }

            if (isset($filter['apply']) && is_callable($filter['apply'])) {
                $filter['apply']($query, $value);

                continue;
            }

            $column = $filter['column'] ?? $key;
            $operator = $filter['operator'] ?? '=';
            $type = $filter['type'] ?? 'text';

            if ($type === 'date') {
                $query->where($column, $operator, Carbon::parse($value));
                continue;
            }

            if ($type === 'text' && $operator === 'like') {
                $query->where($column, 'like', sprintf('%%%s%%', $value));

                continue;
            }

            $query->where($column, $operator, $value);
        }
    }

    private function csvWriter(Builder $query, array $definition): \Closure
    {
        $headers = $this->columnHeaders($definition);

        return function () use ($query, $definition, $headers): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $query->chunk(500, function ($rows) use ($handle, $definition): void {
                foreach ($rows as $row) {
                    $values = $this->formatRow($row, $definition);
                    $stringified = array_map(fn ($value) => $this->stringifyCsvValue($value), $values);
                    fputcsv($handle, $stringified);
                }
            });

            fclose($handle);
        };
    }

    private function jsonWriter(Builder $query, array $definition): \Closure
    {
        return function () use ($query, $definition): void {
            echo '[';
            $first = true;

            $query->chunk(500, function ($rows) use (&$first, $definition): void {
                foreach ($rows as $row) {
                    $values = $this->formatRow($row, $definition);
                    if (! $first) {
                        echo ',';
                    }

                    echo json_encode($values, JSON_UNESCAPED_UNICODE);
                    $first = false;
                }
            });

            echo ']';
        };
    }

    private function columnHeaders(array $definition): array
    {
        $headers = [];

        foreach ($definition['columns'] as $key => $column) {
            $headers[] = is_int($key) ? (string) $column : (string) $key;
        }

        return $headers;
    }

    private function formatRow($row, array $definition): array
    {
        $record = [];

        foreach ($definition['columns'] as $key => $column) {
            $header = is_int($key) ? (string) $column : (string) $key;

            $value = is_callable($column)
                ? $column($row)
                : data_get($row, $column);

            $record[$header] = $value;
        }

        return $record;
    }

    private function stringifyCsvValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        return (string) $value;
    }

    private function parseFile(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'json') {
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
        }

        $rows = [];
        $handle = fopen($path, 'rb');

        if (! $handle) {
            return $rows;
        }

        $headers = null;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($header) => Str::of((string) $header)
                    ->ltrim("\xEF\xBB\xBF")
                    ->trim()
                    ->lower()
                    ->replace(' ', '_')
                    ->value(), $data);
                continue;
            }

            if (empty(array_filter($data, fn ($value) => $value !== null && $value !== ''))) {
                continue;
            }

            if (count($headers) !== count($data)) {
                continue;
            }

            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function decodePayload($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : ['raw' => $value];
    }

    private function parseDate(?string $value): Carbon
    {
        if (! $value) {
            return now();
        }

        return Carbon::parse($value);
    }

    private function toNullableInt($value): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function assertTeacherScope(array $definition, User $user, array $filters): void
    {
        if (! $this->isTeacherAdmin($user) || $this->isAdmin($user)) {
            return;
        }

        $required = $definition['teacher_scope_fields'] ?? [];

        if (empty($required)) {
            return;
        }

        $hasScope = collect($required)
            ->contains(fn ($key) => isset($filters[$key]) && $filters[$key] !== '');

        if (! $hasScope) {
            throw new AuthorizationException('Selecciona curso o lección antes de exportar.');
        }
    }

    private function isAdmin(User $user): bool
    {
        return $user->can('manage-settings');
    }

    private function isTeacherAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('teacher_admin');
    }
}

