<?php

namespace App\Livewire\Student;

use App\Support\Practice\PracticeCart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PracticeCartPage extends Component
{
    public Collection $items;
    public float $subtotal = 0.0;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
        $this->refreshCart();
    }

    public function remove(int $productId): void
    {
        PracticeCart::remove($productId);
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function clearCart(): void
    {
        PracticeCart::clear();
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function proceedToCheckout(): void
    {
        if ($this->items->isEmpty()) {
            $this->addError('cart', __('Tu carrito está vacío.'));

            return;
        }

        $this->redirectRoute('shop.checkout', ['locale' => app()->getLocale()]);
    }

    public function render()
    {
        return view('livewire.student.practice-cart-page');
    }

    protected function refreshCart(): void
    {
        $this->items = PracticeCart::products();
        $this->subtotal = (float) $this->items->sum('price_amount');
    }
}

