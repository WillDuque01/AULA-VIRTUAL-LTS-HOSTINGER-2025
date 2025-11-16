<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PageManager;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
    }

    public function test_admin_can_create_page_from_manager(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        Livewire::test(PageManager::class)
            ->set('title', 'Landing Test')
            ->set('type', 'landing')
            ->set('locale', 'es')
            ->call('create');

        $this->assertDatabaseHas('pages', ['title' => 'Landing Test', 'type' => 'landing']);
    }
}


