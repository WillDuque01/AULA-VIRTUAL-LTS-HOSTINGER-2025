<?php

namespace App\Notifications;

use App\Models\DiscordPractice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscordPracticeSlotAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DiscordPractice $practice)
    {
        $this->practice = $practice->loadMissing('lesson.chapter.course');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lessonTitle = data_get($this->practice->lesson->config, 'title', __('Lección solicitada'));
        $course = $this->practice->lesson->chapter?->course?->slug;

        return (new MailMessage)
            ->subject(__('¡Hay un nuevo cupo para tu práctica solicitada!'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('Abrimos un nuevo slot para ":lesson"', ['lesson' => $lessonTitle]))
            ->line(__('Inicio: :date', ['date' => optional($this->practice->start_at)->translatedFormat('d M H:i')]))
            ->line(__('Curso / cohorte: :course', ['course' => $course ?: __('General')]))
            ->action(__('Reservar ahora'), route('student.discord-practices', ['locale' => app()->getLocale()]))
            ->line(__('Te recomendamos confirmar cuanto antes: los cupos se asignan por orden de reserva.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'practice_id' => $this->practice->id,
            'lesson_id' => $this->practice->lesson_id,
            'title' => $this->practice->title,
            'start_at' => optional($this->practice->start_at)->toIso8601String(),
        ];
    }
}

