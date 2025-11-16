<?php

namespace Tests\Feature\Shop;

use App\Livewire\Student\PracticeCheckout;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_checkout_cart(): void
    {
        $user = User::factory()->create();
        $package = PracticePackage::factory()->create([
            'status' => 'published',
            'price_amount' => 59,
            'price_currency' => 'USD',
            'sessions_count' => 4,
        ]);
        $productId = $package->product?->id;
        $this->assertNotNull($productId, 'Expected practice package to have an associated product.');

        $this->actingAs($user);
        session(['commerce_cart' => [$productId]]);

        Livewire::test(PracticeCheckout::class)
            ->set('paymentMethod', 'card')
            ->call('process')
            ->assertRedirect(route('shop.checkout.success', ['locale' => app()->getLocale()], false));

        $this->assertDatabaseHas('practice_package_orders', [
            'practice_package_id' => $package->id,
            'user_id' => $user->id,
            'status' => 'paid',
        ]);

        $this->assertEmpty(session('commerce_cart', []));
    }
}


