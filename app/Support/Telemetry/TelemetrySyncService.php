<?php

namespace App\Support\Telemetry;

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

    public function syncVideoEvents(int $limit = 200): int
    {
        $events = VideoPlayerEvent::query()
            ->whereNull('synced_at')
            ->orderBy('recorded_at')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            return 0;
        }

        $activeDrivers = collect($this->drivers)
            ->filter(fn (TelemetryDriver $driver) => $driver->enabled())
            ->values();

        if ($activeDrivers->isEmpty()) {
            Log::warning('Telemetry sync skipped: no drivers enabled.');

            return 0;
        }

        $activeDrivers->each(function (TelemetryDriver $driver) use ($events): void {
            $driver->send($events);
        });

        VideoPlayerEvent::whereIn('id', $events->pluck('id'))
            ->update(['synced_at' => now()]);

        return $events->count();
    }
}

