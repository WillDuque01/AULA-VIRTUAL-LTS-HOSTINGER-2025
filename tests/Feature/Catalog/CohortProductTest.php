<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Student\PracticeCheckout;
use App\Models\CohortTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CohortProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_cohort_template_syncs_product_and_checkout_creates_registration(): void
    {
        $user = User::factory()->create();

        $template = CohortTemplate::factory()->create([
            'status' => 'published',
            'price_amount' => 149,
            'price_currency' => 'USD',
            'is_featured' => true,
        ]);

        $this->assertNotNull($template->product, 'Expected cohort template to create a product.');

        $productId = $template->product->id;

        $this->actingAs($user);
        session(['commerce_cart' => [$productId]]);

        Livewire::test(PracticeCheckout::class)
            ->set('paymentMethod', 'card')
            ->call('process')
            ->assertRedirect(route('shop.checkout.success', ['locale' => app()->getLocale()], false));

        $this->assertDatabaseHas('cohort_registrations', [
            'cohort_template_id' => $template->id,
            'user_id' => $user->id,
            'status' => 'paid',
        ]);

        $template->refresh();

        $this->assertEquals(1, $template->enrolled_count);
        $this->assertEquals(
            max(0, $template->capacity - 1),
            $template->product->inventory
        );
    }
}


