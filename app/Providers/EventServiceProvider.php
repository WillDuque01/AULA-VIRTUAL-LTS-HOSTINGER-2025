<?php

namespace App\Providers;

use App\Events\CertificateIssued;
use App\Events\CourseUnlocked;
use App\Events\ModuleUnlocked;
use App\Events\OfferLaunched;
use App\Events\PaymentSimulated;
use App\Events\SubscriptionExpiring;
use App\Events\SubscriptionExpired;
use App\Events\TierUpdated;
use App\Listeners\DispatchCelebrationNotification;
use App\Listeners\EnqueueCertificateIntegrationEvent;
use App\Listeners\LogSimulatedPayment;
use App\Listeners\SendCourseUnlockedNotification;
use App\Listeners\SendModuleUnlockedNotification;
use App\Listeners\SendOfferLaunchedNotification;
use App\Listeners\SendCertificateIssuedNotification;
use App\Listeners\SendSimulatedPaymentNotification;
use App\Listeners\SendSubscriptionExpiringNotification;
use App\Listeners\SendSubscriptionExpiredNotification;
use App\Listeners\SendTierUpdatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentSimulated::class => [
            LogSimulatedPayment::class,
            SendSimulatedPaymentNotification::class,
        ],
        CourseUnlocked::class => [
            SendCourseUnlockedNotification::class,
        ],
        ModuleUnlocked::class => [
            SendModuleUnlockedNotification::class,
        ],
        OfferLaunched::class => [
            SendOfferLaunchedNotification::class,
        ],
        TierUpdated::class => [
            SendTierUpdatedNotification::class,
        ],
        SubscriptionExpiring::class => [
            SendSubscriptionExpiringNotification::class,
        ],
        SubscriptionExpired::class => [
            SendSubscriptionExpiredNotification::class,
        ],
        \App\Events\LessonCompleted::class => [
            DispatchCelebrationNotification::class,
        ],
        CertificateIssued::class => [
            SendCertificateIssuedNotification::class,
            EnqueueCertificateIntegrationEvent::class,
        ],
    ];
}
