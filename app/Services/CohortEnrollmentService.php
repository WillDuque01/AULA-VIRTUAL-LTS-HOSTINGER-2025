<?php

namespace App\Services;

use App\Exceptions\CohortSoldOutException;
use App\Models\CohortRegistration;
use App\Models\CohortTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CohortEnrollmentService
{
    public function enroll(
        User $user,
        CohortTemplate $template,
        float $amount,
        string $currency,
        ?string $reference = null,
        array $meta = []
    ): CohortRegistration {
        return DB::transaction(function () use ($user, $template, $amount, $currency, $reference, $meta): CohortRegistration {
            $template = CohortTemplate::query()
                ->lockForUpdate()
                ->find($template->getKey());

            if (! $template) {
                throw new ModelNotFoundException('Cohort template not found.');
            }

            $registration = CohortRegistration::firstOrNew([
                'cohort_template_id' => $template->getKey(),
                'user_id' => $user->getKey(),
            ]);

            $alreadyConfirmed = in_array($registration->status, ['paid', 'confirmed'], true);

            $template->refreshEnrollmentMetrics();

            if (! $alreadyConfirmed && $template->isSoldOut()) {
                throw CohortSoldOutException::forTemplate($template);
            }

            $registration->fill([
                'status' => 'paid',
                'payment_reference' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'meta' => $meta,
            ]);

            $registration->save();

            $template->refreshEnrollmentMetrics();

            return $registration->fresh();
        });
    }
}

