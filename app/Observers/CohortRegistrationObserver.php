<?php

namespace App\Observers;

use App\Models\CohortRegistration;

class CohortRegistrationObserver
{
    public function created(CohortRegistration $registration): void
    {
        $this->syncTemplate($registration);
    }

    public function updated(CohortRegistration $registration): void
    {
        $this->syncTemplate($registration);
    }

    public function deleted(CohortRegistration $registration): void
    {
        $this->syncTemplate($registration);
    }

    protected function syncTemplate(CohortRegistration $registration): void
    {
        $registration->cohortTemplate?->refreshEnrollmentMetrics();
    }
}


