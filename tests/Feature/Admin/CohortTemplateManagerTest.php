<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CohortTemplateManager;
use App\Models\CohortTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CohortTemplateManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
    }

    public function test_admin_can_create_template(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        Livewire::actingAs($admin)
            ->test(CohortTemplateManager::class)
            ->set('form.name', 'Cohorte Matutina')
            ->set('form.description', 'Sesiones lunes y miércoles')
            ->set('form.slots', [
                ['weekday' => 'monday', 'time' => '09:00'],
                ['weekday' => 'wednesday', 'time' => '09:00'],
            ])
            ->call('save')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('cohort_templates', [
            'name' => 'Cohorte Matutina',
            'slug' => Str::slug('Cohorte Matutina'),
        ]);
    }

    public function test_admin_can_edit_and_delete_template(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $template = CohortTemplate::factory()->create([
            'name' => 'Cohorte Vespertina',
            'slug' => 'cohorte-vespertina',
        ]);

        Livewire::actingAs($admin)
            ->test(CohortTemplateManager::class)
            ->call('edit', $template->id)
            ->set('form.name', 'Cohorte Vespertina Plus')
            ->call('save')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('cohort_templates', [
            'id' => $template->id,
            'name' => 'Cohorte Vespertina Plus',
        ]);

        Livewire::actingAs($admin)
            ->test(CohortTemplateManager::class)
            ->call('delete', $template->id)
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('cohort_templates', [
            'id' => $template->id,
        ]);
    }
}


