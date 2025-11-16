<?php

namespace App\Notifications;

use App\Models\PracticePackageOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticePackagePurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly PracticePackageOrder $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $package = $this->order->package;

        $actionUrl = $package->lesson_id
            ? url(route('lessons.player', ['lesson' => $package->lesson_id, 'locale' => app()->getLocale()]))
            : url(route('dashboard'));

        $name = data_get($notifiable, 'name', __('estudiante'));

        return (new MailMessage())
            ->subject(__('Compra confirmada: :title', ['title' => $package->title]))
            ->greeting(__('Hola :name', ['name' => $name]))
            ->line(__('Has adquirido el pack ":title" con :count sesiones.', [
                'title' => $package->title,
                'count' => $package->sessions_count,
            ]))
            ->line(__('Tu código de referencia es :code.', ['code' => $this->order->payment_reference]))
            ->action(__('Reservar sesiones'), $actionUrl)
            ->line(__('Recuerda elegir tus fechas en el calendario Discord para aprovechar tu pack.'));
    }
}


