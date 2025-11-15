<?php

namespace App\Providers;

use App\Events\PaymentSimulated;
use App\Events\SubscriptionExpiring;
use App\Events\SubscriptionExpired;
use App\Listeners\LogSimulatedPayment;
use App\Listeners\SendSimulatedPaymentNotification;
use App\Listeners\SendSubscriptionExpiringNotification;
use App\Listeners\SendSubscriptionExpiredNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentSimulated::class => [
            LogSimulatedPayment::class,
            SendSimulatedPaymentNotification::class,
        ],
        SubscriptionExpiring::class => [
            SendSubscriptionExpiringNotification::class,
        ],
        SubscriptionExpired::class => [
            SendSubscriptionExpiredNotification::class,
        ],
    ];
}
