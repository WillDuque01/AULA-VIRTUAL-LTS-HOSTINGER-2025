@once
    <style>
        [data-inline-edit] {
            outline: none;
        }
        [data-inline-edit][data-placeholder]:empty::before {
            content: attr(data-placeholder);
            color: #94a3b8;
        }
        [data-inline-edit][data-multiline] {
            white-space: pre-wrap;
            min-height: 2.25rem;
        }
        [data-page-builder-canvas] {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        [data-page-builder-canvas][data-state="dragging"] {
            border-color: rgb(147 197 253);
            box-shadow: 0 25px 45px rgba(15, 23, 42, 0.08);
        }
    </style>
@endonce

<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('page_builder.header.label') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $page->title }}</h1>
                <p class="text-sm text-slate-500">{{ __('page_builder.header.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-500">
                    <button type="button"
                            wire:click="setPreviewMode('desktop')"
                            class="{{ $previewMode === 'desktop' ? 'text-slate-900' : '' }}">
                        🖥
                    </button>
                    <button type="button"
                            wire:click="setPreviewMode('tablet')"
                            class="{{ $previewMode === 'tablet' ? 'text-slate-900' : '' }}">
                        📱
                    </button>
                    <button type="button"
                            wire:click="setPreviewMode('mobile')"
                            class="{{ $previewMode === 'mobile' ? 'text-slate-900' : '' }}">
                        📲
                    </button>
                </div>
                <button type="button"
                        wire:click="saveDraft"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('page_builder.actions.save_draft') }}
                </button>
                <button type="button"
                        wire:click="publish"
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500"
                        wire:loading.attr="disabled"
                        wire:target="publish">
                    <span wire:loading wire:target="publish" class="animate-spin">⏳</span>
                    {{ __('page_builder.actions.publish') }}
                </button>
            </div>
        </div>
        @if($flashMessage)
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ $flashMessage }}
            </div>
        @endif
    </header>

    @php
        $builderTheme = $theme ?? [];
    @endphp
    <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('page_builder.canvas.title') }}</p>
                <p class="text-sm text-slate-500">{{ __('page_builder.canvas.description') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1">
                    ⇅ {{ __('page_builder.canvas.reorder_hint') }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1">
                    ✎ {{ __('page_builder.canvas.edit_hint') }}
                </span>
            </div>
        </div>
        <div class="rounded-[2.5rem] border border-dashed border-slate-200 bg-slate-50/70 p-4 sm:p-6">
            <div
                class="space-y-4"
                data-page-builder-canvas
                data-state="idle"
                style="
                    --page-primary: {{ $builderTheme['primary'] ?? '#0f172a' }};
                    --page-secondary: {{ $builderTheme['secondary'] ?? '#14b8a6' }};
                    --page-background: {{ $builderTheme['background'] ?? '#f8fafc' }};
                    --page-font: {{ $builderTheme['font_family'] ?? 'Inter, sans-serif' }};
                ">
                @forelse($blocks as $index => $block)
                    @php
                        $blockUid = $block['uid'] ?? $index;
                    @endphp
                    <article
                        class="group relative rounded-[2rem] border border-transparent bg-white shadow-sm transition hover:border-indigo-200/80 hover:shadow-lg"
                        data-canvas-block
                        data-block-uid="{{ $blockUid }}">
                        <div class="absolute left-5 top-5 z-10 flex items-center gap-2 text-[11px] font-semibold text-slate-500 opacity-0 transition group-hover:opacity-100">
                            <span class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1" data-canvas-handle aria-label="{{ __('page_builder.canvas.drag_label') }}">
                                ⇅
                            </span>
                            <span class="rounded-full border border-slate-100 bg-white px-3 py-1">{{ \Illuminate\Support\Str::headline($block['type']) }}</span>
                        </div>
                        <div class="rounded-[2rem] bg-white p-4 sm:p-6 md:p-8">
                            @php
                                $previewView = 'livewire.admin.page-builder.preview.'.($block['type'] ?? '');
                            @endphp
                            @includeFirst(
                                [$previewView, 'page.blocks.'.($block['type'] ?? 'hero')],
                                [
                                    'index' => $index,
                                    'block' => $block,
                                    'props' => $block['props'] ?? [],
                                    'isBuilderPreview' => true,
                                ]
                            )
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-slate-200 bg-white/80 p-8 text-center text-sm text-slate-500">
                        {{ __('page_builder.canvas.empty_state') }}
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
        <aside class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm space-y-6">
            <div class="space-y-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('page_builder.theme.title') }}</h2>
                @if(!empty($presets))
                    <div class="flex flex-wrap gap-2">
                        @foreach($presets as $key => $preset)
                            <button type="button"
                                    wire:click="applyPreset('{{ $key }}')"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                                {{ $preset['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('page_builder.theme.primary') }}
                    <input type="color" wire:model.defer="settings.theme.primary" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('page_builder.theme.secondary') }}
                    <input type="color" wire:model.defer="settings.theme.secondary" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('page_builder.theme.background') }}
                    <input type="color" wire:model.defer="settings.theme.background" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('page_builder.theme.font') }}
                    <input type="text"
                           wire:model.defer="settings.theme.font_family"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>

            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('page_builder.kits.title') }}</h2>
                <div class="mt-3 space-y-2">
                    @foreach($kits as $key => $kit)
                        <button type="button"
                                wire:click="addBlock('{{ $key }}')"
                                class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:border-slate-400">
                            {{ $kit['label'] }}
                            <span class="block text-xs font-normal text-slate-400">{{ ucfirst($kit['type']) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        @php
            $previewClass = match($previewMode) {
                'tablet' => 'max-w-3xl mx-auto',
                'mobile' => 'max-w-md mx-auto',
                default => ''
            };
        @endphp
        <section class="space-y-4 {{ $previewClass }}" wire:sortable="reorderBlocks">
            @forelse($blocks as $index => $block)
                <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4"
                         wire:sortable.item="{{ $block['uid'] ?? $index }}"
                         wire:key="builder-block-{{ $block['uid'] ?? $index }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
                                {{ \Illuminate\Support\Str::headline($block['type']) }}
                            </p>
                            <p class="text-sm text-slate-500">{{ __('page_builder.blocks.kit_label', ['kit' => $block['kit'] ?? __('page_builder.blocks.custom_label')]) }}</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold">
                            <button type="button" wire:click="moveBlock({{ $index }}, 'up')" class="rounded-full border border-slate-200 px-2 py-1 text-slate-500 hover:border-slate-400">↑</button>
                            <button type="button" wire:click="moveBlock({{ $index }}, 'down')" class="rounded-full border border-slate-200 px-2 py-1 text-slate-500 hover:border-slate-400">↓</button>
                            <button type="button" wire:click="duplicateBlock({{ $index }})" class="rounded-full border border-slate-200 px-2 py-1 text-slate-500 hover:border-slate-400">⎘</button>
                            <button type="button" wire:click="removeBlock({{ $index }})" class="rounded-full border border-rose-200 px-2 py-1 text-rose-600 hover:border-rose-300">✕</button>
                            <span class="cursor-move rounded-full border border-slate-200 px-2 py-1 text-slate-500" wire:sortable.handle>☰</span>
                        </div>
                    </div>

                    @includeWhen($block['type'] === 'hero', 'livewire.admin.page-builder.blocks.hero-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'cta', 'livewire.admin.page-builder.blocks.cta-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'pricing', 'livewire.admin.page-builder.blocks.pricing-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'testimonials', 'livewire.admin.page-builder.blocks.testimonials-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'featured-products', 'livewire.admin.page-builder.blocks.featured-products-editor', ['index' => $index, 'block' => $block, 'productsCatalog' => $productsCatalog ?? []])
                    @includeWhen($block['type'] === 'gallery', 'livewire.admin.page-builder.blocks.gallery-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'team', 'livewire.admin.page-builder.blocks.team-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'faq', 'livewire.admin.page-builder.blocks.faq-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'timeline', 'livewire.admin.page-builder.blocks.timeline-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'lead-form', 'livewire.admin.page-builder.blocks.lead-form-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'video-testimonial', 'livewire.admin.page-builder.blocks.video-testimonial-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'countdown', 'livewire.admin.page-builder.blocks.countdown-editor', ['index' => $index, 'block' => $block])
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white/50 p-6 text-center text-sm text-slate-500">
                    {{ __('page_builder.blocks.empty_state') }}
                </div>
            @endforelse
        </section>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-Ros5pTKty+O+kO5OVwOB1p5MNDoAuCEi0aKBslZx2XY=" crossorigin="anonymous"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        document.addEventListener('livewire:load', () => {
            const initCanvasSortable = () => {
                const canvas = document.querySelector('[data-page-builder-canvas]');
                if (!canvas || typeof Sortable === 'undefined') {
                    return;
                }

                if (canvas._sortable) {
                    canvas._sortable.destroy();
                }

                const componentId = canvas.closest('[wire\\:id]')?.getAttribute('wire:id');
                if (!componentId) {
                    return;
                }

                const callReorder = () => {
                    const instance = window.Livewire?.find(componentId);
                    if (!instance) {
                        return;
                    }

                    const payload = Array.from(canvas.querySelectorAll('[data-canvas-block]')).map((block) => ({
                        value: block.dataset.blockUid,
                    }));

                    instance.call('reorderBlocks', payload);
                };

                canvas._sortable = Sortable.create(canvas, {
                    handle: '[data-canvas-handle]',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    onChoose: () => canvas.setAttribute('data-state', 'dragging'),
                    onUnchoose: () => canvas.setAttribute('data-state', 'idle'),
                    onEnd: () => {
                        canvas.setAttribute('data-state', 'idle');
                        callReorder();
                    },
                });
            };

            initCanvasSortable();

            Livewire.hook('morph.updated', (component) => {
                if (component.el?.querySelector('[data-page-builder-canvas]')) {
                    setTimeout(initCanvasSortable, 60);
                }
            });
        });
    </script>
@endpush
