<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PageBuilderEditor;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageBuilderEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Admin');
    }

    public function test_admin_can_add_block_and_save_draft(): void
    {
        $page = Page::factory()->create([
            'title' => 'Home ES',
            'slug' => 'home-es',
            'type' => 'home',
            'locale' => 'es',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        Livewire::test(PageBuilderEditor::class, ['page' => $page])
            ->call('addBlock', 'hero_simple')
            ->call('saveDraft')
            ->assertSet('flashMessage', __('Borrador guardado.'));

        $this->assertDatabaseHas('page_revisions', [
            'page_id' => $page->id,
        ]);
    }
}


