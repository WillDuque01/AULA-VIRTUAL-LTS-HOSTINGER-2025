<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VideoPlayerEvent;
use App\Notifications\Telemetry\TelemetryBacklogAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class MonitorTelemetryBacklogCommand extends Command
{
    protected $signature = 'telemetry:monitor-backlog';

    protected $description = 'Monitorea el backlog de telemetría y envía alertas cuando supera el umbral configurado.';

    public function handle(): int
    {
        $threshold = (int) config('services.telemetry_alerts.threshold', 4000);
        $cooldown = (int) config('services.telemetry_alerts.cooldown_minutes', 60);

        $pending = VideoPlayerEvent::whereNull('synced_at')->count();
        $this->info(sprintf('Eventos pendientes: %d', $pending));

        if ($pending < $threshold) {
            $this->info('Dentro del umbral. No se envían alertas.');

            return self::SUCCESS;
        }

        $cacheKey = 'telemetry:backlog:last-alert';
        if (Cache::has($cacheKey)) {
            $this->warn('Ya se envió una alerta recientemente. Se omite para evitar spam.');

            return self::SUCCESS;
        }

        $recipients = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'teacher_admin']))->get();
        if ($recipients->isEmpty()) {
            $this->warn('No hay destinatarios con rol Admin/Teacher Admin para recibir la alerta.');

            return self::SUCCESS;
        }

        Notification::send($recipients, new TelemetryBacklogAlertNotification($pending, $threshold));
        Cache::put($cacheKey, now(), now()->addMinutes($cooldown));

        $this->info('Alerta enviada.');

        return self::SUCCESS;
    }
}


