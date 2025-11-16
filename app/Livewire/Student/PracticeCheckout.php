<?php

namespace App\Livewire\Student;

use App\Models\PracticePackage;
use App\Services\PracticePackageOrderService;
use App\Support\Practice\PracticeCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class PracticeCheckout extends Component
{
    public Collection $items;
    public float $total = 0.0;
    public string $paymentMethod = 'card';
    public ?string $notes = null;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
        $this->loadCart();
        if ($this->items->isEmpty()) {
            $this->redirectRoute('shop.cart', ['locale' => app()->getLocale()]);
        }
    }

    public function render()
    {
        return view('livewire.student.practice-checkout');
    }

    public function process(PracticePackageOrderService $service): void
    {
        $this->validate([
            'paymentMethod' => ['required', 'in:card,transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->items->isEmpty()) {
            $this->redirectRoute('shop.cart', ['locale' => app()->getLocale()]);

            return;
        }

        try {
            DB::transaction(function () use ($service): void {
                $user = Auth::user();

                foreach ($this->items as $product) {
                    $resource = $product->productable;

                    if ($resource instanceof PracticePackage) {
                        $order = $service->createPendingOrder($user->id, $resource);
                        $service->markAsPaid($order, 'SHOP-'.Str::upper(Str::random(8)));
                    }
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            if (app()->environment('testing')) {
                throw $exception;
            }
            session()->flash('checkout_error', __('No pudimos procesar tu pago. Intenta nuevamente o contáctanos.'));
            $this->redirectRoute('shop.checkout.failed', ['locale' => app()->getLocale()]);

            return;
        }

        $summary = [
            'count' => $this->items->count(),
            'total' => $this->total,
            'method' => $this->paymentMethod,
        ];

        PracticeCart::clear();
        session()->flash('checkout_success', $summary);
        $this->redirectRoute('shop.checkout.success', ['locale' => app()->getLocale()]);
    }

    protected function loadCart(): void
    {
        $this->items = PracticeCart::products();
        $this->total = (float) $this->items->sum('price_amount');
    }
}


