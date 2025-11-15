<?php

namespace App\Listeners;

use App\Events\OfferLaunched;
use App\Notifications\OfferLaunchedNotification;
use App\Support\Integrations\IntegrationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendOfferLaunchedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OfferLaunched $event): void
    {
        if ($event->recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $event->recipients,
            new OfferLaunchedNotification(
                $event->offerTitle,
                $event->offerDescription,
                $event->tierLabel,
                $event->offerUrl,
                $event->validUntil,
                $event->price,
                $event->discount,
                $event->intro
            )
        );

        IntegrationDispatcher::dispatch('offer.launched', [
            'offer_title' => $event->offerTitle,
            'description' => $event->offerDescription,
            'tier_label' => $event->tierLabel,
            'offer_url' => $event->offerUrl,
            'valid_until' => $event->validUntil,
            'price' => $event->price,
            'discount' => $event->discount,
            'recipient_ids' => $event->recipients->pluck('id')->filter()->values()->all(),
            'recipient_emails' => $event->recipients->pluck('email')->filter()->values()->all(),
        ]);
    }
}

