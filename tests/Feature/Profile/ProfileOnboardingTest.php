<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('teacher');
        Role::findOrCreate('teacher_admin');
    }

    public function test_banner_shows_for_incomplete_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
        ]);

        $this->actingAs($user);

        $this->get('/es/dashboard')
            ->assertOk()
            ->assertSee('Completa tu perfil');
    }

    public function test_profile_update_persists_new_fields_and_completes_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
        ]);

        $this->actingAs($user)
            ->patch('/es/profile', [
                'first_name' => 'Ana',
                'last_name' => 'Pérez',
                'email' => 'ana@example.com',
                'phone' => '+51 900 000 000',
                'country' => 'Perú',
                'state' => 'Lima',
                'city' => 'Lima',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'name' => 'Ana Pérez',
            'phone' => '+51 900 000 000',
            'profile_completion_score' => 100,
        ]);
    }

    public function test_teacher_fields_are_displayed(): void
    {
        $teacher = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
        ]);
        $teacher->assignRole('teacher');

        $this->actingAs($teacher)
            ->get('/es/profile')
            ->assertOk()
            ->assertSee('Teacher profile');
    }
}
