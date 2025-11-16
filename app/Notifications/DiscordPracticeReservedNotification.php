<?php

namespace App\Notifications;

use App\Models\DiscordPractice;
use App\Models\DiscordPracticeReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscordPracticeReservedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DiscordPractice $practice,
        private readonly DiscordPracticeReservation $reservation
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->reservation->user;
        $startAt = optional($this->practice->start_at)->timezone($notifiable->timezone ?? config('app.timezone'));

        return (new MailMessage())
            ->subject(__('Nueva reserva en tu práctica :title', ['title' => $this->practice->title]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name]))
            ->line(__('Se confirmó la reserva de :student para la sesión del :date.', [
                'student' => $student?->name ?? __('un estudiante'),
                'date' => optional($startAt)->translatedFormat('d M H:i') ?? __('próxima fecha'),
            ]))
            ->line(__('Capacidad actual: :reserved / :capacity', [
                'reserved' => $this->practice->reservations()->count(),
                'capacity' => $this->practice->capacity,
            ]))
            ->action(__('Ver prácticas'), url(route('professor.discord-practices', ['locale' => app()->getLocale()])))
            ->line(__('Puedes gestionar la sesión desde tu panel de profesor.'));
    }
}


