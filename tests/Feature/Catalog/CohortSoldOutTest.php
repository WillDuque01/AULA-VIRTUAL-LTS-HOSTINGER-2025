<?php

namespace Tests\Feature\Catalog;

use App\Exceptions\CohortSoldOutException;
use App\Livewire\Student\PracticeCheckout;
use App\Models\CohortTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CohortSoldOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_prevents_overbooking_cohort(): void
    {
        $template = CohortTemplate::factory()->create([
            'capacity' => 1,
            'status' => 'published',
            'price_amount' => 120,
            'price_currency' => 'USD',
        ]);

        $productId = $template->product->id;

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser);
        session(['commerce_cart' => [$productId]]);

        Livewire::test(PracticeCheckout::class)
            ->set('paymentMethod', 'card')
            ->call('process')
            ->assertRedirect(route('shop.checkout.success', ['locale' => app()->getLocale()], false));

        $template->refresh();

        $this->assertSame(1, $template->enrolled_count);
        $this->assertSame(0, $template->remainingSlots());

        $this->actingAs($secondUser);
        session(['commerce_cart' => [$productId]]);

        $this->expectException(CohortSoldOutException::class);

        Livewire::test(PracticeCheckout::class)
            ->set('paymentMethod', 'card')
            ->call('process');
    }
}


