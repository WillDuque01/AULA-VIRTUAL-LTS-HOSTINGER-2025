<?php

namespace Tests\Feature;

use App\Livewire\Admin\GroupManager;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class GroupManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-settings', fn () => true);
    }

    public function test_admin_can_create_group_with_students(): void
    {
        $admin = User::factory()->create();

        $tier = Tier::factory()->create();
        $students = User::factory(2)->create();

        Livewire::actingAs($admin)
            ->test(GroupManager::class)
            ->call('createGroup')
            ->set('form.name', 'Cohorte Pro Febrero')
            ->set('form.slug', 'cohorte-pro-febrero')
            ->set('form.tier_id', $tier->id)
            ->set('form.description', 'Grupo de onboarding intensivo')
            ->set('form.capacity', 25)
            ->set('selectedStudents', $students->pluck('id')->all())
            ->call('saveGroup')
            ->assertHasNoErrors();

        $group = StudentGroup::where('slug', 'cohorte-pro-febrero')->first();
        $this->assertNotNull($group);
        $this->assertEquals($tier->id, $group->tier_id);
        $this->assertCount(2, $group->students);
    }

    public function test_admin_can_toggle_group_status(): void
    {
        $admin = User::factory()->create();
        $group = StudentGroup::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(GroupManager::class)
            ->call('toggleActive', $group->id)
            ->assertHasNoErrors();

        $this->assertFalse($group->fresh()->is_active);
    }
}
