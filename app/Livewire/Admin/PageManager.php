<?php

namespace App\Livewire\Admin;

use App\Models\Page;
use App\Services\PageBuilderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class PageManager extends Component
{
    use WithPagination;

    public string $type = 'landing';

    public string $locale = 'es';

    public ?string $title = null;

    public function create(PageBuilderService $service): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:140'],
            'locale' => ['required', 'in:es,en'],
            'type' => ['required', 'in:home,landing,custom'],
        ]);

        $slug = Str::slug($data['title']);
        if ($data['type'] !== 'home') {
            $slug .= '-'.Str::random(4);
        }

        $page = $service->createPage([
            'title' => $data['title'],
            'slug' => $slug,
            'type' => $data['type'],
            'locale' => $data['locale'],
            'status' => 'draft',
        ], [], [], Auth::id());

        $this->reset('title');
        $this->dispatch('notify', message: __('Página creada. Redirigiendo...'));
        $this->redirectRoute('admin.pages.builder', ['page' => $page->id, 'locale' => app()->getLocale()]);
    }

    public function duplicate(int $pageId): void
    {
        $page = Page::findOrFail($pageId);
        $copy = $page->replicate(['slug', 'status', 'published_revision_id', 'meta']);
        $copy->slug = Str::slug($page->slug.'-copy-'.Str::random(3));
        $copy->status = 'draft';
        $copy->save();

        if ($latest = $page->latestRevision()->first()) {
            $copy->revisions()->create([
                'label' => __('Duplicado de :title', ['title' => $page->title]),
                'layout' => $latest->layout,
                'settings' => $latest->settings,
                'author_id' => Auth::id(),
            ]);
        }

        $this->dispatch('notify', message: __('Página duplicada'));
    }

    public function render()
    {
        return view('livewire.admin.page-manager', [
            'pages' => Page::query()
                ->orderByDesc('updated_at')
                ->paginate(12),
        ]);
    }
}


