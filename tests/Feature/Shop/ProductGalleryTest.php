<?php

namespace Tests\Feature\Shop;

use App\Livewire\Shop\ProductGallery;
use App\Models\PracticePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_catalog(): void
    {
        $package = PracticePackage::factory()->create(['status' => 'published']);

        Livewire::test(ProductGallery::class)
            ->assertSee($package->title);
    }

    public function test_guest_can_add_featured_product_to_cart(): void
    {
        $package = PracticePackage::factory()->create(['status' => 'published']);
        $product = $package->product;

        Livewire::test(ProductGallery::class)
            ->call('addToCart', $product->id)
            ->assertSet('flash', __('Producto agregado al carrito. Completa tu compra desde el checkout.'));

        $this->assertSame([$product->id], session('commerce_cart'));
    }
}


