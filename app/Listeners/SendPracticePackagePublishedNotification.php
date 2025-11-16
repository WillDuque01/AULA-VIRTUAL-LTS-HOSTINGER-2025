<?php

namespace App\Listeners;

use App\Events\PracticePackagePublished;
use App\Notifications\PracticePackagePublishedNotification;

class SendPracticePackagePublishedNotification
{
    public function handle(PracticePackagePublished $event): void
    {
        $package = $event->package->load('creator');

        if ($package->creator) {
            $package->creator->notify(new PracticePackagePublishedNotification($package));
        }
    }
}


