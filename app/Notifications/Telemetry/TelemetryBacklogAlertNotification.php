<?php

namespace App\Notifications\Telemetry;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TelemetryBacklogAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $pending,
        protected int $threshold
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Backlog de telemetría excedido'))
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?? 'equipo']))
            ->line(__('Hay :pending eventos de player pendientes de sincronizar (umbral :threshold).', [
                'pending' => number_format($this->pending),
                'threshold' => number_format($this->threshold),
            ]))
            ->line(__('Ejecuta `telemetry:sync --limit=500` desde DataPorter o verifica los drivers configurados.'))
            ->action(__('Abrir DataPorter'), route('admin.data-porter', ['locale' => app()->getLocale()]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pending' => $this->pending,
            'threshold' => $this->threshold,
            'message' => __('Backlog de telemetría excedido'),
        ];
    }
}


