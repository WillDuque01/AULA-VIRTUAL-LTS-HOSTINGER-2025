<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly AssignmentSubmission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assignment = $this->submission->assignment;
        $lesson = $assignment?->lesson;
        $course = $lesson?->chapter?->course;

        return (new MailMessage())
            ->subject(__('¡Tu tarea ha sido aprobada!'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('La actividad ":assignment" fue aprobada con :score pts.', [
                'assignment' => data_get($lesson?->config, 'title', __('Tarea')),
                'score' => $this->submission->score ?? '--',
            ]))
            ->line(__('Puedes continuar con el módulo de :course.', ['course' => $course?->slug ?? __('tu curso')]))
            ->action(__('Ir al dashboard'), route('dashboard', ['locale' => app()->getLocale()]))
            ->line(__('¡Sigue con el excelente trabajo!'));
    }
}


