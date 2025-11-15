<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardCertificatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_recent_certificate_verifications(): void
    {
        Role::create(['name' => 'teacher_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('teacher_admin');

        $student = User::factory()->create(['name' => 'Alumno Verificado']);
        $course = Course::create([
            'slug' => 'curso-verificado',
            'level' => 'b2',
            'published' => true,
        ]);

        Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'code' => 'VER123',
            'file_path' => 'certificates/demo.pdf',
            'issued_at' => now()->subDays(2),
            'verified_count' => 3,
            'last_verified_at' => now()->subMinutes(5),
        ]);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee(__('dashboard.certificates.recent_verifications'))
            ->assertSee('VER123')
            ->assertSee('Alumno Verificado');
    }
}


