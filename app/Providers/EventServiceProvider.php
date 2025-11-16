<?php

namespace App\Providers;

use App\Events\AssignmentApproved;
use App\Events\AssignmentRejected;
use App\Events\CertificateIssued;
use App\Events\CourseUnlocked;
use App\Events\ModuleUnlocked;
use App\Events\OfferLaunched;
use App\Events\PaymentSimulated;
use App\Events\DiscordPracticeRequestEscalated;
use App\Events\DiscordPracticeReserved;
use App\Events\DiscordPracticeScheduled;
use App\Events\PracticePackagePublished;
use App\Events\PracticePackagePurchased;
use App\Events\PracticePackageSessionConsumed;
use App\Events\SubscriptionExpiring;
use App\Events\SubscriptionExpired;
use App\Events\TierUpdated;
use App\Listeners\DispatchCelebrationNotification;
use App\Listeners\EnqueueCertificateIntegrationEvent;
use App\Listeners\EnqueueAssignmentApprovedIntegrationEvent;
use App\Listeners\EnqueueAssignmentRejectedIntegrationEvent;
use App\Listeners\LogSimulatedPayment;
use App\Listeners\EnqueueDiscordPracticeRequestEscalatedEvent;
use App\Listeners\EnqueueDiscordPracticeReservedEvent;
use App\Listeners\EnqueueDiscordPracticeScheduledEvent;
use App\Listeners\EnqueuePracticePackagePublishedEvent;
use App\Listeners\EnqueuePracticePackagePurchasedEvent;
use App\Listeners\SendCourseUnlockedNotification;
use App\Listeners\SendDiscordPracticeRequestEscalatedNotification;
use App\Listeners\SendDiscordPracticeReservedNotification;
use App\Listeners\SendDiscordPracticeScheduledNotification;
use App\Listeners\SendModuleUnlockedNotification;
use App\Listeners\SendOfferLaunchedNotification;
use App\Listeners\SendCertificateIssuedNotification;
use App\Listeners\SendAssignmentApprovedNotification;
use App\Listeners\SendAssignmentRejectedNotification;
use App\Listeners\SendPracticePackagePublishedNotification;
use App\Listeners\RecordPracticePackPurchaseSnapshot;
use App\Listeners\RecordPracticeReservationSnapshot;
use App\Listeners\RecordPracticeRequestEscalationSnapshot;
use App\Listeners\RecordPracticeSessionConsumedSnapshot;
use App\Listeners\SendPracticePackagePurchasedNotification;
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
        AssignmentApproved::class => [
            SendAssignmentApprovedNotification::class,
            EnqueueAssignmentApprovedIntegrationEvent::class,
        ],
        AssignmentRejected::class => [
            SendAssignmentRejectedNotification::class,
            EnqueueAssignmentRejectedIntegrationEvent::class,
        ],
        PracticePackagePublished::class => [
            SendPracticePackagePublishedNotification::class,
            EnqueuePracticePackagePublishedEvent::class,
        ],
        PracticePackagePurchased::class => [
            SendPracticePackagePurchasedNotification::class,
            EnqueuePracticePackagePurchasedEvent::class,
            RecordPracticePackPurchaseSnapshot::class,
        ],
        PracticePackageSessionConsumed::class => [
            RecordPracticeSessionConsumedSnapshot::class,
        ],
        DiscordPracticeScheduled::class => [
            SendDiscordPracticeScheduledNotification::class,
            EnqueueDiscordPracticeScheduledEvent::class,
        ],
        DiscordPracticeReserved::class => [
            SendDiscordPracticeReservedNotification::class,
            EnqueueDiscordPracticeReservedEvent::class,
            RecordPracticeReservationSnapshot::class,
        ],
        DiscordPracticeRequestEscalated::class => [
            SendDiscordPracticeRequestEscalatedNotification::class,
            EnqueueDiscordPracticeRequestEscalatedEvent::class,
            RecordPracticeRequestEscalationSnapshot::class,
        ],
    ];
}
