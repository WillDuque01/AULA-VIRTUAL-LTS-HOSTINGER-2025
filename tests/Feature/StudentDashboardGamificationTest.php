<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentDashboardGamificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_recent_gamification_feed(): void
    {
        Role::create(['name' => 'student_paid']);

        $user = User::factory()->create([
            'experience_points' => 120,
            'current_streak' => 3,
        ]);
        $user->assignRole('student_paid');

        \App\Models\GamificationEvent::create([
            'user_id' => $user->id,
            'type' => 'lesson_completed',
            'points' => 50,
            'metadata' => ['badge' => '🔥 Racha x3', 'streak' => 3],
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('120')
            ->assertSee('Racha')
            ->assertSee('🔥 Racha x3');
    }
}


