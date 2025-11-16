<?php

namespace Tests\Feature;

use App\Livewire\Student\PracticePackagesCatalog;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PracticePackagesCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_highlights_package_when_parameter_is_present(): void
    {
        $user = User::factory()->create();

        $package = PracticePackage::create([
            'creator_id' => $user->id,
            'title' => 'Pack recomendado',
            'sessions_count' => 3,
            'price_amount' => 90,
            'price_currency' => 'USD',
            'is_global' => true,
            'status' => 'published',
        ]);

        Livewire::actingAs($user)
            ->test(PracticePackagesCatalog::class, ['highlightPackageId' => $package->id])
            ->assertSet('highlightPackageId', $package->id);
    }

    public function test_auto_open_checkout_when_auto_open_flag_is_true(): void
    {
        $user = User::factory()->create();

        $package = PracticePackage::create([
            'creator_id' => $user->id,
            'title' => 'Pack intensivo',
            'sessions_count' => 4,
            'price_amount' => 120,
            'price_currency' => 'USD',
            'is_global' => true,
            'status' => 'published',
        ]);

        Livewire::actingAs($user)
            ->test(PracticePackagesCatalog::class, [
                'highlightPackageId' => $package->id,
                'autoOpenHighlight' => true,
            ])
            ->assertSet('showCheckout', true)
            ->assertSet('checkoutPackageId', $package->id);
    }
}


