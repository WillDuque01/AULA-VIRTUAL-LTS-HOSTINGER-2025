<?php

namespace App\Notifications;

use App\Models\DiscordPractice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscordPracticeScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly DiscordPractice $practice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $startAt = optional($this->practice->start_at)->timezone($notifiable->timezone ?? config('app.timezone'));

        return (new MailMessage())
            ->subject(__('Slot publicado: :title', ['title' => $this->practice->title]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name]))
            ->line(__('Se abrió una nueva práctica para :lesson', [
                'lesson' => data_get($this->practice->lesson?->config, 'title', __('lección')),
            ]))
            ->line(__('Inicio: :date · Capacidad: :capacity estudiantes', [
                'date' => optional($startAt)->translatedFormat('d M H:i') ?? __('por definir'),
                'capacity' => $this->practice->capacity,
            ]))
            ->action(__('Gestionar calendario'), url(route('professor.discord-practices', ['locale' => app()->getLocale()])))
            ->line(__('Comparte el enlace con tus estudiantes para llenar los cupos.'));
    }
}


