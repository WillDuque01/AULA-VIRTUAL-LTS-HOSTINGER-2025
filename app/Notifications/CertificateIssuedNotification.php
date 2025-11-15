<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Certificate $certificate)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('certificates.show', ['locale' => app()->getLocale(), 'certificate' => $this->certificate]);

        return (new MailMessage())
            ->subject(__('¡Tu certificado está listo!'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('Completaste el curso :course. Generamos tu certificado automáticamente.', [
                'course' => $this->certificate->course->slug,
            ]))
            ->action(__('Descargar certificado'), $url)
            ->line(__('Código de verificación: :code', ['code' => $this->certificate->code]));
    }
}


