<?php

namespace App\Listeners;

use App\Events\OfferLaunched;
use App\Notifications\OfferLaunchedNotification;
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
    }
}

