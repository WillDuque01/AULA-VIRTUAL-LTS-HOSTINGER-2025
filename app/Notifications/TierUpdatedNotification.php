<?php

namespace App\Notifications;

use App\Models\Tier;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TierUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(private readonly Tier $tier)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->renderMail(
            'emails.templates.subscription-status',
            [
                'notifiable' => $notifiable,
                'subscription' => null,
                'tierName' => $this->tier->name,
                'statusLabel' => __('Active'),
                'renewsAt' => null,
                'expiresAt' => null,
                'ctaUrl' => url('/catalog'),
                'ctaLabel' => 'email.actions.open_dashboard',
                'intro' => __('email.body.tier_updated.intro', ['tier' => $this->tier->name]),
                'additionalContent' => __('email.body.generic.cta_dashboard'),
            ],
            'email.subjects.tier_updated',
            ['tier' => $this->tier->name]
        );
    }
}
