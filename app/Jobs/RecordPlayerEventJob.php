<?php
// [AGENTE: OPUS 4.5] - Job para procesar eventos de telemetría de forma asíncrona

namespace App\Jobs;

use App\Models\VideoPlayerEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordPlayerEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de intentos antes de fallar.
     */
    public int $tries = 3;

    /**
     * Tiempo máximo de ejecución en segundos.
     */
    public int $timeout = 30;

    public function __construct(
        private readonly int $userId,
        private readonly int $lessonId,
        private readonly ?int $courseId,
        private readonly array $eventData
    ) {
    }

    public function handle(): void
    {
        VideoPlayerEvent::create([
            'user_id' => $this->userId,
            'lesson_id' => $this->lessonId,
            'course_id' => $this->courseId,
            'event' => $this->eventData['event'] ?? 'custom',
            'provider' => $this->eventData['provider'] ?? 'unknown',
            'playback_seconds' => isset($this->eventData['playback_seconds']) ? (int) $this->eventData['playback_seconds'] : 0,
            'watched_seconds' => isset($this->eventData['watched_seconds']) ? (int) $this->eventData['watched_seconds'] : 0,
            'video_duration' => isset($this->eventData['video_duration']) ? (int) $this->eventData['video_duration'] : null,
            'playback_rate' => isset($this->eventData['playback_rate']) ? (float) $this->eventData['playback_rate'] : 1.0,
            'context_tag' => $this->eventData['context_tag'] ?? 'player',
            'metadata' => $this->eventData['metadata'] ?? [],
            'recorded_at' => $this->eventData['recorded_at'] ?? now(),
        ]);
    }

    /**
     * Cola específica para telemetría.
     */
    public function queue(): string
    {
        return 'telemetry';
    }
}

