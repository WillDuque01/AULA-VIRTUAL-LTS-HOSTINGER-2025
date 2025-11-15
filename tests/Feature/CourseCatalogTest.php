<?php

namespace Tests\Feature;

use App\Livewire\Catalog\CourseCatalog;
use App\Models\Course;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CourseCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_purchase_tier(): void
    {
        $tier = Tier::factory()->create(['access_type' => 'paid', 'price_monthly' => 15, 'slug' => 'pro']);
        $course = Course::create(['slug' => 'marketing-avanzado', 'level' => 'advanced', 'published' => true]);
        $course->tiers()->attach($tier->id);

        Livewire::test(CourseCatalog::class)
            ->call('purchaseTier', $tier->id)
            ->assertSet('flashError', __('Debes iniciar sesion para comprar acceso.'));
    }

    public function test_user_can_simulate_purchase_and_gain_access(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $tier = Tier::factory()->create(['access_type' => 'paid', 'price_monthly' => 25, 'slug' => 'gold']);
        $group = StudentGroup::factory()->create(['tier_id' => $tier->id]);

        $course = Course::create(['slug' => 'analytics-pro', 'level' => 'pro', 'published' => true]);
        $course->tiers()->attach($tier->id);

        Livewire::actingAs($user)
            ->test(CourseCatalog::class)
            ->call('purchaseTier', $tier->id)
            ->assertSet('flashStatus', __('Acceso comprado correctamente.'));

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tier_user', [
            'user_id' => $user->id,
            'tier_id' => $tier->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => $user->id,
            'student_group_id' => $group->id,
        ]);
    }
}
