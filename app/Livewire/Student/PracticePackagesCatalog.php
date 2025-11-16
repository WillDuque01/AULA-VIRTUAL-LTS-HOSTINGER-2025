<?php

namespace App\Livewire\Student;

use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Services\PracticePackageOrderService;
use Illuminate\Support\Collection;
use Livewire\Component;

class PracticePackagesCatalog extends Component
{
    public Collection $packages;
    public Collection $orders;

    public ?int $checkoutPackageId = null;
    public bool $showCheckout = false;

    public function mount(): void
    {
        $this->loadPackages();
        $this->loadOrders();
    }

    public function loadPackages(): void
    {
        $user = auth()->user();

        $query = PracticePackage::query()
            ->where('status', 'published')
            ->orderByDesc('is_global')
            ->orderBy('price_amount');

        if ($user) {
            $teacherIds = $this->teacherIdsFor($user);
            $query->where(function ($q) use ($teacherIds) {
                $q->where('is_global', true)
                    ->orWhereIn('creator_id', $teacherIds);
            });
        } else {
            $query->where('is_global', true);
        }

        $this->packages = $query->get();
    }

    private function teacherIdsFor($user): array
    {
        $tiers = $user->tiers()
            ->wherePivot('status', 'active')
            ->get();

        return $tiers->pluck('pivot.assigned_by')->filter()->unique()->values()->all();
    }

    public function loadOrders(): void
    {
        if (! auth()->check()) {
            $this->orders = collect();

            return;
        }

        $this->orders = PracticePackageOrder::with('package')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['paid', 'completed'])
            ->orderByDesc('paid_at')
            ->get();
    }

    public function startCheckout(int $packageId)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->checkoutPackageId = $packageId;
        $this->showCheckout = true;
    }

    public function confirmCheckout(PracticePackageOrderService $service): void
    {
        if (! $this->checkoutPackageId) {
            return;
        }

        $package = PracticePackage::findOrFail($this->checkoutPackageId);
        $user = auth()->user();

        $order = PracticePackageOrder::firstOrCreate(
            [
                'practice_package_id' => $package->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        $service->markAsPaid($order);

        $this->showCheckout = false;
        $this->checkoutPackageId = null;

        $this->loadOrders();
        $this->dispatch('package-purchased');
    }

    public function render()
    {
        return view('livewire.student.practice-packages-catalog');
    }
}


