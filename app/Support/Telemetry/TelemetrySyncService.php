<?php

namespace App\Support\Telemetry;

use App\Models\TelemetrySyncLog;
use App\Models\VideoPlayerEvent;
use App\Support\Telemetry\Drivers\TelemetryDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TelemetrySyncService
{
    /**
     * @param array<int,TelemetryDriver> $drivers
     */
    public function __construct(private readonly array $drivers)
    {
    }

    public function syncVideoEvents(int $limit = 200, ?int $triggeredBy = null): int
    {
        $startedAt = microtime(true);
        $processed = 0;

        $events = VideoPlayerEvent::query()
            ->whereNull('synced_at')
            ->orderBy('recorded_at')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            $this->logSync('skipped', 0, 0, 'Sin eventos pendientes.', $triggeredBy, $startedAt);

            return 0;
        }

        $activeDrivers = collect($this->drivers)
            ->filter(fn (TelemetryDriver $driver) => $driver->enabled())
            ->values();

        $driverCount = $activeDrivers->count();

        if ($driverCount === 0) {
            $message = 'No hay drivers habilitados.';
            Log::warning('Telemetry sync skipped: no drivers enabled.');
            $this->logSync('skipped', 0, 0, $message, $triggeredBy, $startedAt);

            return 0;
        }

        try {
            $activeDrivers->each(function (TelemetryDriver $driver) use ($events): void {
                $driver->send($events);
            });

            VideoPlayerEvent::whereIn('id', $events->pluck('id'))
                ->update(['synced_at' => now()]);

            $processed = $events->count();
            $message = sprintf('Eventos sincronizados: %d', $processed);

            $this->logSync('success', $processed, $driverCount, $message, $triggeredBy, $startedAt);

            return $processed;
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            $this->logSync('failed', $processed, $driverCount, $message, $triggeredBy, $startedAt);

            throw $exception;
        }
    }

    private function logSync(string $status, int $processed, int $driverCount, ?string $message, ?int $triggeredBy, float $startedAt): void
    {
        TelemetrySyncLog::create([
            'status' => $status,
            'processed' => $processed,
            'driver_count' => $driverCount,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            'message' => $message,
            'triggered_by' => $triggeredBy,
        ]);
    }
}

