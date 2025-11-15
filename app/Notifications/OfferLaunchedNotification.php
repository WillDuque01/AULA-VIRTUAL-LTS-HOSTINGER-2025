<?php

namespace App\Notifications;

use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferLaunchedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(
        private readonly string $offerTitle,
        private readonly string $offerDescription,
        private readonly string $tierLabel,
        private readonly ?string $offerUrl = null,
        private readonly ?string $validUntil = null,
        private readonly ?string $price = null,
        private readonly ?string $discount = null,
        private readonly ?string $intro = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->renderMail(
            'emails.templates.offer-announcement',
            [
                'notifiable' => $notifiable,
                'offerTitle' => $this->offerTitle,
                'offerDescription' => $this->offerDescription,
                'audienceLabel' => $this->tierLabel,
                'offerUrl' => $this->offerUrl ?? url('/pricing'),
                'validUntil' => $this->validUntil,
                'price' => $this->price,
                'discount' => $this->discount,
                'intro' => $this->intro ?? __('email.body.offer_launched.intro'),
            ],
            'email.subjects.offer_launched',
            ['tier' => $this->tierLabel]
        );
    }
}
