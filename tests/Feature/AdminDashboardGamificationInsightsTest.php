<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardGamificationInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_lists_top_xp_and_streaks(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'teacher_admin']);
        $admin->assignRole('teacher_admin');

        User::factory()->create([
            'name' => 'Ana XP',
            'experience_points' => 200,
            'current_streak' => 2,
        ]);

        User::factory()->create([
            'name' => 'Luis Streak',
            'experience_points' => 150,
            'current_streak' => 5,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee('Ana XP')
            ->assertSee('Luis Streak')
            ->assertSee('+200 XP')
            ->assertSee('5 🔥');
    }
}


