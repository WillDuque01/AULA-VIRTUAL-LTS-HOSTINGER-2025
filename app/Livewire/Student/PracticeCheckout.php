<?php

namespace App\Livewire\Student;

use App\Exceptions\CohortSoldOutException;
use App\Models\Page;
use App\Models\PageConversion;
use App\Models\CohortTemplate;
use App\Models\PracticePackage;
use App\Services\PracticePackageOrderService;
use App\Services\CohortEnrollmentService;
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

    public function process(PracticePackageOrderService $service, CohortEnrollmentService $cohortEnrollment): void
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
            DB::transaction(function () use ($service, $cohortEnrollment): void {
                $user = Auth::user();

                foreach ($this->items as $product) {
                    $resource = $product->productable;

                    if ($resource instanceof PracticePackage) {
                        $order = $service->createPendingOrder($user->id, $resource);
                        $service->markAsPaid($order, 'SHOP-'.Str::upper(Str::random(8)));
                    } elseif ($resource instanceof CohortTemplate) {
                        $cohortEnrollment->enroll(
                            $user,
                            $resource,
                            (float) $product->price_amount,
                            $product->price_currency,
                            'COHORT-'.Str::upper(Str::random(8)),
                            [
                                'product_id' => $product->id,
                                'product_title' => $product->title,
                            ]
                        );
                    }
                }

                $this->logLandingConversion();
            });
        } catch (CohortSoldOutException $exception) {
            report($exception);
            if (app()->environment('testing')) {
                throw $exception;
            }
            session()->flash('checkout_error', $exception->getMessage());
            $this->redirectRoute('shop.cart', ['locale' => app()->getLocale()]);

            return;
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

    protected function logLandingConversion(): void
    {
        $slug = session('landing_ref');

        if (! $slug || $this->items->isEmpty() || $this->total <= 0) {
            return;
        }

        $page = Page::query()->where('slug', $slug)->first();

        if (! $page) {
            return;
        }

        PageConversion::create([
            'page_id' => $page->id,
            'amount' => $this->total,
            'currency' => $this->items->first()->price_currency ?? 'USD',
            'meta' => [
                'items' => $this->items->pluck('title')->all(),
            ],
        ]);

        session()->forget('landing_ref');
    }
}


