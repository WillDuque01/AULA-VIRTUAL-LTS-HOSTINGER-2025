<?php

namespace App\Livewire\Public;

use App\Models\PracticePackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class PracticePackagesShowcase extends Component
{
    public Collection $packages;

    public function mount(): void
    {
        if (! Schema::hasTable('practice_packages')) {
            $this->packages = collect();

            return;
        }

        $this->packages = PracticePackage::where('status', 'published')
            ->where('is_global', true)
            ->orderBy('price_amount')
            ->take(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.public.practice-packages-showcase');
    }
}


