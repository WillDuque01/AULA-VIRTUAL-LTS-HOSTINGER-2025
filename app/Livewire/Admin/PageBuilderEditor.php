<?php

namespace App\Livewire\Admin;

use App\Models\Page;
use App\Services\PageBuilderService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class PageBuilderEditor extends Component
{
    public Page $page;

    public array $blocks = [];

    public ?string $flashMessage = null;

    public array $kits = [];

    public bool $isPublishing = false;

    public function mount(Page $page): void
    {
        $this->page = $page->load(['revisions' => fn ($q) => $q->latest(), 'publishedRevision']);
        $this->kits = config('page_builder.kits', []);
        $this->blocks = $this->prepareBlocks($this->page->currentLayout());
    }

    public function addBlock(string $kitKey): void
    {
        $kit = $this->kits[$kitKey] ?? null;
        if (! $kit) {
            return;
        }

        $this->blocks[] = [
            'uid' => (string) Str::uuid(),
            'type' => $kit['type'],
            'kit' => $kitKey,
            'props' => $kit['props'],
        ];
    }

    public function duplicateBlock(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $copy = $this->blocks[$index];
        $copy['uid'] = (string) Str::uuid();
        array_splice($this->blocks, $index + 1, 0, [$copy]);
    }

    public function removeBlock(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        array_splice($this->blocks, $index, 1);
        $this->blocks = array_values($this->blocks);
    }

    public function moveBlock(int $index, string $direction): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($this->blocks)) {
            return;
        }

        $blocks = $this->blocks;
        [$blocks[$index], $blocks[$target]] = [$blocks[$target], $blocks[$index]];
        $this->blocks = array_values($blocks);
    }

    public function updateProp(int $index, string $path, $value): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        data_set($this->blocks[$index], $path, $value);
    }

    public function saveDraft(PageBuilderService $service): void
    {
        $service->saveDraft($this->page, [
            'label' => now()->format('d M H:i'),
            'layout' => $this->sanitizedBlocks(),
            'settings' => [],
        ], Auth::id());

        $this->flashMessage = __('Borrador guardado.');
        $this->dispatch('notify', message: $this->flashMessage);
    }

    public function publish(PageBuilderService $service): void
    {
        $this->isPublishing = true;
        $revision = $service->saveDraft($this->page, [
            'label' => __('Versión publicada :date', ['date' => now()->format('d/m H:i')]),
            'layout' => $this->sanitizedBlocks(),
            'settings' => [],
        ], Auth::id());

        $service->publish($this->page, $revision);
        $this->isPublishing = false;
        $this->page->refresh();

        $this->flashMessage = __('Página publicada correctamente.');
        $this->dispatch('notify', message: $this->flashMessage);
    }

    public function render()
    {
        return view('livewire.admin.page-builder-editor', [
            'blocks' => $this->blocks,
            'kits' => $this->kits,
        ]);
    }

    protected function prepareBlocks(array $blocks): array
    {
        foreach ($blocks as &$block) {
            if (($block['type'] ?? null) === 'pricing') {
                $items = Arr::get($block, 'props.items', []);
                foreach ($items as $idx => $item) {
                    $items[$idx]['features_text'] = implode(PHP_EOL, Arr::get($item, 'features', []));
                }
                Arr::set($block, 'props.items', $items);
            }
        }

        return $blocks;
    }

    protected function sanitizedBlocks(): array
    {
        $blocks = $this->blocks;

        foreach ($blocks as &$block) {
            if (($block['type'] ?? null) === 'pricing') {
                $items = Arr::get($block, 'props.items', []);
                foreach ($items as $idx => $item) {
                    $features = collect(preg_split('/\r\n|\r|\n/', $item['features_text'] ?? ''))
                        ->map(fn ($line) => trim($line))
                        ->filter()
                        ->values()
                        ->all();
                    $items[$idx]['features'] = $features;
                    unset($items[$idx]['features_text']);
                }
                Arr::set($block, 'props.items', $items);
            }
        }

        return $blocks;
    }
}


