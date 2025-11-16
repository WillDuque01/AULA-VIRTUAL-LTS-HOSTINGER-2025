<?php

namespace App\Listeners;

use App\Events\PracticePackagePurchased;
use App\Notifications\PracticePackagePurchasedNotification;
use Illuminate\Support\Facades\Notification;

class SendPracticePackagePurchasedNotification
{
    public function handle(PracticePackagePurchased $event): void
    {
        $order = $event->order->load('package.creator', 'user');

        if ($order->user) {
            $order->user->notify(new PracticePackagePurchasedNotification($order));
        }

        if ($order->package?->creator) {
            Notification::route('mail', $order->package->creator->email)
                ->notify(new PracticePackagePurchasedNotification($event->order));
        }
    }
}


