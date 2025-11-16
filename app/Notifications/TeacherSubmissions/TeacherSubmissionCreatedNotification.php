<?php

namespace App\Notifications\TeacherSubmissions;

use App\Models\TeacherSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherSubmissionCreatedNotification extends Notification implements ShouldQueue
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
        $locale = app()->getLocale();
        $route = route('admin.teacher-submissions', ['locale' => $locale], false);

        return (new MailMessage)
            ->subject(__('Nueva propuesta docente: :title', ['title' => $this->submission->title]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?? __('equipo')]))
            ->line(__('Se recibió una nueva propuesta de :teacher para el curso :course.', [
                'teacher' => $this->submission->author?->name ?? $this->submission->author?->email,
                'course' => $this->submission->course?->slug ?? __('sin curso'),
            ]))
            ->line(__('Tipo de propuesta: :type', ['type' => ucfirst($this->submission->type)]))
            ->action(__('Revisar ahora'), $route)
            ->line(__('Puedes aprobarla o devolver feedback desde el Administrador → Revisiones docentes.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'title' => $this->submission->title,
            'type' => $this->submission->type,
            'teacher' => $this->submission->author?->name ?? $this->submission->author?->email,
            'status' => $this->submission->status,
            'route' => route('admin.teacher-submissions', ['locale' => app()->getLocale()], false),
        ];
    }
}

