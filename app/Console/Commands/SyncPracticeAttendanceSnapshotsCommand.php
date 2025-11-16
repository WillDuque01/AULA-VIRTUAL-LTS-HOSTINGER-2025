<?php

namespace App\Console\Commands;

use App\Models\DiscordPractice;
use App\Support\Analytics\TelemetryRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncPracticeAttendanceSnapshotsCommand extends Command
{
    protected $signature = 'practices:sync-attendance {--limit=25 : Número máximo de prácticas a procesar}';

    protected $description = 'Registra snapshots de asistencia y cancelaciones tardías de las prácticas de Discord.';

    public function handle(TelemetryRecorder $recorder): int
    {
        $graceMinutes = (int) config('services.discord_practices.attendance_grace_minutes', 45);
        $lateCancelMinutes = (int) config('services.discord_practices.late_cancel_minutes', 180);

        $practices = DiscordPractice::with([
                'lesson.chapter.course',
                'reservations' => fn ($query) => $query->with('user'),
            ])
            ->whereNull('attendance_synced_at')
            ->where('start_at', '<=', now()->subMinutes($graceMinutes))
            ->limit((int) $this->option('limit'))
            ->get();

        if ($practices->isEmpty()) {
            $this->info('No hay prácticas pendientes de sincronización.');

            return self::SUCCESS;
        }

        foreach ($practices as $practice) {
            $lesson = $practice->lesson;
            $course = $lesson?->chapter?->course;

            foreach ($practice->reservations as $reservation) {
                if ($reservation->status === 'confirmed') {
                    $recorder->recordStudentSnapshot($reservation->user_id, 'practice_attendance', [
                        'course_id' => $course?->id,
                        'lesson_id' => $lesson?->id,
                        'practice_package_id' => $practice->practice_package_id,
                        'scope' => 'discord_practice',
                        'value' => 1,
                        'payload' => [
                            'practice_id' => $practice->id,
                            'reservation_id' => $reservation->id,
                            'status' => $reservation->status,
                            'start_at' => optional($practice->start_at)->toIso8601String(),
                            'attended_at' => now()->toIso8601String(),
                        ],
                    ]);

                    continue;
                }

                if ($reservation->status === 'cancelled') {
                    $cancelledAt = $reservation->cancelled_at ?? $reservation->updated_at;

                    $isLate = $cancelledAt instanceof Carbon
                        && optional($practice->start_at)?->diffInMinutes($cancelledAt, false) > (-1 * $lateCancelMinutes);

                    $recorder->recordStudentSnapshot($reservation->user_id, 'practice_cancellation', [
                        'course_id' => $course?->id,
                        'lesson_id' => $lesson?->id,
                        'practice_package_id' => $practice->practice_package_id,
                        'scope' => 'discord_practice',
                        'value' => 1,
                        'payload' => [
                            'practice_id' => $practice->id,
                            'reservation_id' => $reservation->id,
                            'status' => $reservation->status,
                            'cancelled_at' => optional($cancelledAt)->toIso8601String(),
                            'late' => $isLate,
                            'late_window_minutes' => $lateCancelMinutes,
                        ],
                    ]);
                }
            }

            $practice->attendance_synced_at = now();
            if ($practice->status === 'scheduled') {
                $practice->status = 'completed';
            }
            $practice->save();

            $this->info(sprintf('Práctica %d sincronizada.', $practice->id));
        }

        return self::SUCCESS;
    }
}


