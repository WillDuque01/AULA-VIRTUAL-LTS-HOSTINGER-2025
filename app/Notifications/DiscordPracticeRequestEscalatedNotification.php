<?php

namespace App\Notifications;

use App\Models\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscordPracticeRequestEscalatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Lesson $lesson,
        private readonly int $pendingCount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lessonTitle = data_get($this->lesson->config, 'title', __('lección'));
        $course = $this->lesson->chapter?->course?->slug;

        return (new MailMessage())
            ->subject(__('Hay :count solicitudes pendientes para :lesson', [
                'count' => $this->pendingCount,
                'lesson' => $lessonTitle,
            ]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name]))
            ->line(__('Los estudiantes están pidiendo nuevas prácticas para :lesson (:course).', [
                'lesson' => $lessonTitle,
                'course' => $course,
            ]))
            ->line(__('Solicitudes pendientes: :count', ['count' => $this->pendingCount]))
            ->action(__('Abrir planificador'), url(route('professor.discord-practices', ['locale' => app()->getLocale()])))
            ->line(__('Publica slots adicionales o responde a los alumnos desde el módulo de prácticas.'));
    }
}


