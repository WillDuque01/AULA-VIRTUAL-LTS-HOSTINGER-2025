<?php

namespace Tests\Feature\Product;

use App\Models\PracticePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_practice_package_creates_product_entry(): void
    {
        $package = PracticePackage::factory()->create([
            'title' => 'Pack Intensivo',
            'status' => 'published',
        ]);

        $this->assertNotNull($package->product);
        $this->assertDatabaseHas('products', [
            'productable_id' => $package->id,
            'productable_type' => PracticePackage::class,
            'title' => 'Pack Intensivo',
        ]);
    }
}


