<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ProductCatalog;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
        Role::findOrCreate('teacher_admin');
    }

    public function test_admin_can_toggle_featured_product(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $package = PracticePackage::factory()->create(['status' => 'published']);
        $product = $package->product;

        $this->actingAs($admin);

        Livewire::test(ProductCatalog::class)
            ->call('toggleFeatured', $product->id);

        $this->assertTrue($product->fresh()->is_featured);
    }

    public function test_admin_can_edit_product_metadata(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $package = PracticePackage::factory()->create(['status' => 'published']);
        $product = $package->product;

        $this->actingAs($admin);

        Livewire::test(ProductCatalog::class)
            ->call('edit', $product->id)
            ->set('form.title', 'Nuevo título')
            ->set('form.price_amount', 120)
            ->set('form.status', 'draft')
            ->call('save');

        tap($product->fresh(), function ($updated): void {
            $this->assertSame('Nuevo título', $updated->title);
            $this->assertEquals(120, (float) $updated->price_amount);
            $this->assertSame('draft', $updated->status);
        });
    }
}


