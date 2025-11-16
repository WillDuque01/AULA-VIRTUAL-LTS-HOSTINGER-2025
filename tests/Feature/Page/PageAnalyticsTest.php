<?php

namespace Tests\Feature\Page;

use App\Livewire\Student\PracticeCheckout;
use App\Models\Page;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_view_is_logged(): void
    {
        $this->withoutMiddleware();
        config(['setup.completed' => true]);

        $page = Page::factory()->create([
            'title' => 'Landing Demo',
            'slug' => 'landing-demo',
            'type' => 'landing',
            'locale' => 'es',
            'status' => 'published',
        ]);
        $page->revisions()->create([
            'label' => 'Init',
            'layout' => [],
            'settings' => [],
        ]);
        $page->update(['published_revision_id' => $page->revisions()->first()->id]);

        $response = $this->get('/es/landing/'.$page->slug);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_views', [
            'page_id' => $page->id,
        ]);
    }

    public function test_checkout_records_conversion_for_landing(): void
    {
        $this->withoutMiddleware();
        config(['setup.completed' => true]);

        $page = Page::factory()->create([
            'title' => 'Landing Demo',
            'slug' => 'landing-demo',
            'type' => 'landing',
            'locale' => 'es',
            'status' => 'published',
        ]);
        $revision = $page->revisions()->create([
            'label' => 'Init',
            'layout' => [],
            'settings' => [],
        ]);
        $page->update(['published_revision_id' => $revision->id]);

        $user = User::factory()->create();
        $package = PracticePackage::factory()->create([
            'status' => 'published',
            'price_amount' => 59,
        ]);
        $productId = $package->product->id;

        $this->actingAs($user);
        session([
            'commerce_cart' => [$productId],
            'landing_ref' => $page->slug,
        ]);

        Livewire::test(PracticeCheckout::class)
            ->set('paymentMethod', 'card')
            ->call('process')
            ->assertRedirect(route('shop.checkout.success', ['locale' => app()->getLocale()], false));

        $this->assertDatabaseHas('page_conversions', [
            'page_id' => $page->id,
        ]);
    }
}


