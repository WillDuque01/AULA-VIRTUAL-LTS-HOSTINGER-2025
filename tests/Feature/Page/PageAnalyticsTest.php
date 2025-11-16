<?php

namespace Tests\Feature\Page;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_view_is_logged(): void
    {
        $this->withoutMiddleware();
        config(['setup.completed' => true]);

        $page = Page::factory()->create([
            'title' => 'Landing Demo',
            'slug' => 'landing-demo',
            'type' => 'landing',
            'locale' => 'es',
            'status' => 'published',
        ]);
        $page->revisions()->create([
            'label' => 'Init',
            'layout' => [],
            'settings' => [],
        ]);
        $page->update(['published_revision_id' => $page->revisions()->first()->id]);

        $response = $this->get('/es/landing/'.$page->slug);
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_views', [
            'page_id' => $page->id,
        ]);
    }
}


