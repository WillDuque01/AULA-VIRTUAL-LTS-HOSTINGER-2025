<?php

namespace App\Listeners;

use App\Events\CertificateIssued;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueueCertificateIntegrationEvent
{
    public function handle(CertificateIssued $event): void
    {
        $certificate = $event->certificate;
        $user = $certificate->user;
        $course = $certificate->course;

        $locale = app()->getLocale() ?? config('app.locale');

        IntegrationDispatcher::dispatch('certificate.issued', [
            'certificate' => [
                'code' => $certificate->code,
                'issued_at' => optional($certificate->issued_at)->toIso8601String(),
                'url' => route('certificates.show', [
                    'locale' => $locale,
                    'certificate' => $certificate,
                ]),
            ],
            'student' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
            'course' => [
                'id' => $course?->id,
                'slug' => $course?->slug,
            ],
        ]);
    }
}


