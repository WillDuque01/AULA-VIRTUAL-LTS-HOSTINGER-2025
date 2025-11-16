<?php

namespace App\Observers;

use App\Models\PracticePackage;
use Illuminate\Support\Str;

class PracticePackageObserver
{
    public function created(PracticePackage $package): void
    {
        $this->syncProduct($package);
    }

    public function updated(PracticePackage $package): void
    {
        $this->syncProduct($package);
    }

    public function deleted(PracticePackage $package): void
    {
        $package->product?->delete();
    }

    protected function syncProduct(PracticePackage $package): void
    {
        $title = $package->title ?? __('Pack sin título');
        $slug = Str::slug($title).'-pack-'.$package->getKey();

        $package->product()->updateOrCreate([], [
            'type' => 'practice_pack',
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $package->subtitle,
            'description' => $package->description,
            'status' => $package->status === 'published' ? 'published' : 'draft',
            'category' => $package->is_global ? 'global' : 'cohort',
            'badge' => $package->visibility === 'private' ? __('Privado') : null,
            'thumbnail_path' => data_get($package->meta, 'thumbnail_path'),
            'price_amount' => $package->price_amount,
            'price_currency' => $package->price_currency,
            'compare_at_amount' => data_get($package->meta, 'compare_at_amount'),
            'is_featured' => (bool) data_get($package->meta, 'is_featured', false),
            'meta' => [
                'sessions_count' => $package->sessions_count,
                'practice_package_id' => $package->getKey(),
            ],
        ]);
    }
}


