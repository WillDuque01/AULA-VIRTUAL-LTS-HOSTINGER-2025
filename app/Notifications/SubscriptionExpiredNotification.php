<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(private readonly Subscription $subscription)
    {
        $this->subscription->loadMissing('tier');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tierName = $this->subscription->tier?->name ?? __('Tier');
        $expiresAt = optional($this->subscription->cancelled_at ?? $this->subscription->ends_at)->translatedFormat('d M Y H:i');

        return $this->renderMail(
            'emails.templates.subscription-status',
            [
                'notifiable' => $notifiable,
                'subscription' => $this->subscription,
                'tierName' => $tierName,
                'statusLabel' => __('Expired'),
                'renewsAt' => null,
                'expiresAt' => $expiresAt,
                'ctaUrl' => url('/catalog'),
                'ctaLabel' => 'email.blocks.course.start_learning',
                'intro' => __('email.body.subscription_expired.intro', ['tier' => $tierName]),
                'additionalContent' => __('email.body.subscription_expired.cta'),
            ],
            'email.subjects.subscription_expired',
            ['tier' => $tierName]
        );
    }
}
