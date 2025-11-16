<?php

namespace Tests\Feature\Page;

use App\Models\Page;
use App\Services\PageBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_page_with_initial_revision(): void
    {
        $service = new PageBuilderService();

        $page = $service->createPage([
            'title' => 'Home ES',
            'slug' => 'home-es',
            'type' => 'home',
            'locale' => 'es',
            'status' => 'draft',
        ], [
            ['type' => 'hero', 'props' => ['headline' => 'Hola']],
        ]);

        $this->assertDatabaseHas('pages', ['slug' => 'home-es']);
        $this->assertCount(1, $page->revisions);
    }

    public function test_publish_sets_revision_as_live(): void
    {
        $service = new PageBuilderService();
        $page = $service->createPage([
            'title' => 'Landing Demo',
            'slug' => 'landing-demo',
            'type' => 'landing',
            'locale' => 'es',
        ]);

        $draft = $service->saveDraft($page, [
            'label' => 'Hero actualizado',
            'layout' => [['type' => 'hero', 'props' => ['headline' => 'Nuevo']]],
        ]);

        $updated = $service->publish($page, $draft);

        $this->assertEquals('published', $updated->status);
        $this->assertEquals($draft->id, $updated->published_revision_id);
    }
}


