<?php

namespace Tests\Feature\Auth;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\AuditorProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class RoleGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuditorProfilesSeeder::class);

        Role::firstOrCreate(['name' => 'Admin']);
        $admin = User::whereEmail('academy@letstalkspanish.io')->firstOrFail();
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::whereEmail('academy@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard.admin', ['locale' => 'es']));

        $response->assertOk();
    }

    public function test_student_is_blocked_from_admin_dashboard(): void
    {
        $student = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($student)->get(route('dashboard.admin', ['locale' => 'es']));

        $response->assertForbidden();
    }

    public function test_teacher_admin_can_access_professor_planner(): void
    {
        $teacherAdmin = User::whereEmail('teacher.admin.qa@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($teacherAdmin)->get(route('professor.discord-practices', ['locale' => 'es']));

        $response->assertOk();
    }

    public function test_student_cannot_access_provisioner(): void
    {
        $student = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($student)->get(route('provisioner', ['locale' => 'es']));

        $response->assertForbidden();
    }

    public function test_teacher_admin_cannot_access_student_only_checkout(): void
    {
        $teacherAdmin = User::whereEmail('teacher.admin.qa@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($teacherAdmin)->get(route('shop.checkout', ['locale' => 'es']));

        $response->assertForbidden();
    }

    public function test_student_paid_can_access_student_dashboard(): void
    {
        $student = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();

        $response = $this->actingAs($student)->get(route('dashboard.student', ['locale' => 'es']));

        $response->assertOk();
    }
}

