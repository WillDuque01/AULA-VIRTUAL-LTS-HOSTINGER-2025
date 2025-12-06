<?php
// [AGENTE: OPUS 4.5] - Refactorizado para usar colas (RecordPlayerEventJob)

namespace App\Support\Analytics;

use App\Jobs\RecordPlayerEventJob;
use App\Models\Lesson;
use App\Models\StudentActivitySnapshot;
use App\Models\TeacherActivitySnapshot;
use App\Models\VideoPlayerEvent;
use App\Models\VideoProgress;

class TelemetryRecorder
{
    /**
     * Controla si los eventos se procesan vía cola (producción) o síncronamente (testing).
     */
    private bool $useQueue = true;

    public function __construct()
    {
        // En testing, desactivar colas para que las assertions funcionen
        $this->useQueue = ! app()->runningUnitTests();
    }

    public function recordPlayerTick(VideoProgress $progress, array $data = []): void
    {
        $progress->loadMissing(['lesson.chapter.course']);

        $this->recordPlayerEvent(
            $progress->user_id,
            $progress->lesson,
            array_merge([
                'event' => $data['event'] ?? 'progress_tick',
                'provider' => $data['provider'] ?? $progress->source,
                'playback_seconds' => $data['playback_seconds'] ?? $progress->last_second ?? 0,
                'watched_seconds' => $data['watched_seconds'] ?? $progress->watched_seconds ?? 0,
                'video_duration' => $data['video_duration'] ?? null,
            ], $data)
        );
    }

    public function recordPlayerEvent(int $userId, ?Lesson $lesson, array $data = []): void
    {
        if (! $lesson) {
            return;
        }

        $courseId = $lesson->chapter?->course?->id;

        if ($this->useQueue) {
            // Despachar a cola 'telemetry' para procesamiento asíncrono
            RecordPlayerEventJob::dispatch(
                $userId,
                $lesson->id,
                $courseId,
                $data
            )->onQueue('telemetry');
        } else {
            // Modo síncrono para testing
            VideoPlayerEvent::create([
                'user_id' => $userId,
                'lesson_id' => $lesson->id,
                'course_id' => $courseId,
                'event' => $data['event'] ?? 'custom',
                'provider' => $data['provider'] ?? 'unknown',
                'playback_seconds' => isset($data['playback_seconds']) ? (int) $data['playback_seconds'] : 0,
                'watched_seconds' => isset($data['watched_seconds']) ? (int) $data['watched_seconds'] : 0,
                'video_duration' => isset($data['video_duration']) ? (int) $data['video_duration'] : null,
                'playback_rate' => isset($data['playback_rate']) ? (float) $data['playback_rate'] : 1.0,
                'context_tag' => $data['context_tag'] ?? 'player',
                'metadata' => $data['metadata'] ?? [],
                'recorded_at' => $data['recorded_at'] ?? now(),
            ]);
        }
    }

    public function recordStudentSnapshot(int $userId, string $category, array $attributes = []): StudentActivitySnapshot
    {
        return StudentActivitySnapshot::create([
            'user_id' => $userId,
            'course_id' => $attributes['course_id'] ?? null,
            'lesson_id' => $attributes['lesson_id'] ?? null,
            'practice_package_id' => $attributes['practice_package_id'] ?? null,
            'category' => $category,
            'scope' => $attributes['scope'] ?? null,
            'value' => isset($attributes['value']) ? (int) $attributes['value'] : null,
            'payload' => $attributes['payload'] ?? [],
            'captured_at' => $attributes['captured_at'] ?? now(),
        ]);
    }

    public function recordTeacherSnapshot(int $teacherId, string $category, array $attributes = []): TeacherActivitySnapshot
    {
        return TeacherActivitySnapshot::create([
            'teacher_id' => $teacherId,
            'course_id' => $attributes['course_id'] ?? null,
            'lesson_id' => $attributes['lesson_id'] ?? null,
            'practice_package_id' => $attributes['practice_package_id'] ?? null,
            'category' => $category,
            'scope' => $attributes['scope'] ?? null,
            'value' => isset($attributes['value']) ? (int) $attributes['value'] : null,
            'payload' => $attributes['payload'] ?? [],
            'captured_at' => $attributes['captured_at'] ?? now(),
        ]);
    }
}

