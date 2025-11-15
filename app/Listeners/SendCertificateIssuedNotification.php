<?php

namespace App\Listeners;

use App\Events\CertificateIssued;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendCertificateIssuedNotification implements ShouldQueue
{
    public function handle(CertificateIssued $event): void
    {
        $user = $event->certificate->user;

        if (! $user) {
            return;
        }

        Notification::send($user, new CertificateIssuedNotification($event->certificate));
    }
}


