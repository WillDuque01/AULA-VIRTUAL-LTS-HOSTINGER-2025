<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly AssignmentSubmission $submission, private readonly ?string $reason = null)
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

        $mail = (new MailMessage())
            ->subject(__('Tu tarea necesita ajustes'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('La actividad ":assignment" requiere una nueva entrega.', [
                'assignment' => data_get($lesson?->config, 'title', __('Tarea')),
            ]));

        if ($this->reason) {
            $mail->line(__('Motivo: :reason', ['reason' => $this->reason]));
        }

        if ($this->submission->feedback) {
            $mail->line($this->submission->feedback);
        }

        return $mail
            ->action(__('Ver detalles'), route('dashboard', ['locale' => app()->getLocale()]));
    }
}


