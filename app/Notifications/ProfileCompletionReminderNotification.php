<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileCompletionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly array $summary)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pending = collect($this->summary['steps'] ?? [])
            ->where('completed', false)
            ->pluck('label')
            ->implode(', ');

        return (new MailMessage())
            ->subject(__('Completa tu perfil para mejorar la experiencia'))
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?? __('Teacher')]))
            ->line(__('Tu perfil está completado al :percent%. Esto nos ayuda a personalizar recomendaciones y mostrar información correcta en cohortes.', [
                'percent' => $this->summary['percent'] ?? 0,
            ]))
            ->line(__('Pendientes: :pending', ['pending' => $pending ?: __('solo unos detalles finales')]))
            ->action(__('Completar mi perfil'), route('profile.edit', ['locale' => app()->getLocale()]))
            ->line(__('Gracias por mantener tu información al día.'));
    }
}

