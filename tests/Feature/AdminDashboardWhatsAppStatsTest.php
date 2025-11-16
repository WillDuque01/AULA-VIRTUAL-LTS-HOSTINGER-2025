<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Models\IntegrationEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardWhatsAppStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_whatsapp_widget(): void
    {
        Role::firstOrCreate(['name' => 'teacher_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('teacher_admin');

        IntegrationEvent::factory()->create([
            'event' => 'whatsapp.cta_clicked',
            'payload' => ['context' => 'student.dashboard'],
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee(__('dashboard.whatsapp.title'))
            ->assertSee('1');
    }
}


