<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SimulatedPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(public readonly Subscription $subscription)
    {
        $this->subscription->loadMissing('tier');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tierName = $this->subscription->tier?->name ?? __('email.blocks.payment.plan_default');
        $provider = Str::title((string) $this->subscription->provider);

        return $this->renderMail(
            'emails.templates.payment-confirmation',
            [
                'notifiable' => $notifiable,
                'subscription' => $this->subscription,
                'tierName' => $tierName,
                'statusLabel' => Str::headline($this->subscription->status),
                'amount' => $this->subscription->amount,
                'currency' => $this->subscription->currency,
                'provider' => $provider,
                'dashboardUrl' => url('/dashboard'),
                'intro' => __('email.body.payment_confirmed.intro', ['tier' => $tierName]),
            ],
            'email.subjects.payment_confirmed',
            ['tier' => $tierName]
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'tier_id' => $this->subscription->tier_id,
            'provider' => $this->subscription->provider,
            'status' => $this->subscription->status,
        ];
    }
}
