<?php

namespace App\Notifications\TeacherSubmissions;

use App\Models\TeacherSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherSubmissionStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected TeacherSubmission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->submission->status === 'approved'
            ? __('aprobada')
            : __('rechazada');

        $route = route('dashboard.teacher', ['locale' => app()->getLocale()], false);

        $mail = (new MailMessage)
            ->subject(__('Tu propuesta fue :status', ['status' => $statusLabel]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?? __('docente')]))
            ->line(__('La propuesta ":title" fue :status.', [
                'title' => $this->submission->title,
                'status' => $statusLabel,
            ]));

        if ($this->submission->feedback) {
            $mail->line(__('Feedback: :feedback', [
                'feedback' => $this->submission->feedback,
            ]));
        }

        return $mail->action(__('Ir a mi panel'), $route)
            ->line(__('Gracias por seguir construyendo el programa.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'title' => $this->submission->title,
            'status' => $this->submission->status,
            'feedback' => $this->submission->feedback,
        ];
    }
}

