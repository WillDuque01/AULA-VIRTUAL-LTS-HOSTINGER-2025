<?php

namespace App\Observers;

use App\Models\CohortTemplate;
use Illuminate\Support\Str;

class CohortTemplateObserver
{
    public function created(CohortTemplate $template): void
    {
        $this->syncProduct($template);
    }

    public function updated(CohortTemplate $template): void
    {
        $this->syncProduct($template);
    }

    public function deleted(CohortTemplate $template): void
    {
        $template->product?->delete();
    }

    protected function syncProduct(CohortTemplate $template): void
    {
        $slug = 'cohort-'.$template->slug;

        $template->product()->updateOrCreate([], [
            'type' => 'cohort',
            'title' => $template->name,
            'slug' => Str::slug($slug),
            'excerpt' => $template->description,
            'description' => $template->description,
            'status' => $template->status,
            'category' => $template->cohort_label ?: 'cohort',
            'badge' => $template->requires_package ? __('Pack requerido') : null,
            'price_amount' => $template->price_amount,
            'price_currency' => $template->price_currency,
            'is_featured' => (bool) $template->is_featured,
            'inventory' => $template->remainingSlots(),
            'meta' => [
                'cohort_label' => $template->cohort_label,
                'duration_minutes' => $template->duration_minutes,
                'capacity' => $template->capacity,
                'available_slots' => $template->remainingSlots(),
                'enrolled_count' => $template->enrolled_count,
                'slots' => $template->slots,
            ],
        ]);
    }
}


