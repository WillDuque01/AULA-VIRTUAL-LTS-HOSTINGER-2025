<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Support\Facades\DB;

class PageBuilderService
{
    public function createPage(array $attributes, array $layout = [], array $settings = [], ?int $authorId = null): Page
    {
        return DB::transaction(function () use ($attributes, $layout, $settings, $authorId) {
            $page = Page::create($attributes);

            $revision = $page->revisions()->create([
                'label' => 'Initial draft',
                'layout' => $layout,
                'settings' => $settings,
                'author_id' => $authorId,
            ]);

            if ($page->status === 'published') {
                $page->update(['published_revision_id' => $revision->id]);
            }

            return $page->fresh(['publishedRevision', 'revisions']);
        });
    }

    public function saveDraft(Page $page, array $payload, ?int $authorId = null): PageRevision
    {
        return $page->revisions()->create([
            'label' => $payload['label'] ?? now()->format('d M H:i'),
            'layout' => $payload['layout'] ?? [],
            'settings' => $payload['settings'] ?? [],
            'author_id' => $authorId,
        ]);
    }

    public function publish(Page $page, PageRevision $revision = null): Page
    {
        $revision = $revision ?: $page->revisions()->latest()->first();

        abort_unless($revision, 422, __('No hay una versión para publicar.'));

        $page->update([
            'status' => 'published',
            'published_revision_id' => $revision->id,
        ]);

        return $page->fresh(['publishedRevision']);
    }
}


