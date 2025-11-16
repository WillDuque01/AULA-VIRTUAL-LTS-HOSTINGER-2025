<?php

namespace App\Notifications;

use App\Models\PracticePackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticePackagePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly PracticePackage $package)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('Nuevo pack de prácticas: :title', ['title' => $this->package->title]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name]))
            ->line(__('Tu profesor ha publicado un pack premium con :count sesiones en vivo.', ['count' => $this->package->sessions_count]))
            ->line($this->package->subtitle ?? '')
            ->action(__('Ver detalles'), url(route('dashboard')))
            ->line(__('Reserva tu cupo para asegurar tu lugar en las prácticas de Discord.'));
    }
}


