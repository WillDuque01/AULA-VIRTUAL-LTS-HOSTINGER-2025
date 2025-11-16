<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Page Builder') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $page->title }}</h1>
                <p class="text-sm text-slate-500">{{ __('Arrastra bloques o usa los botones para construir la landing.') }}</p>
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
                    {{ __('Guardar borrador') }}
                </button>
                <button type="button"
                        wire:click="publish"
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500"
                        wire:loading.attr="disabled"
                        wire:target="publish">
                    <span wire:loading wire:target="publish" class="animate-spin">⏳</span>
                    {{ __('Publicar página') }}
                </button>
            </div>
        </div>
        @if($flashMessage)
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ $flashMessage }}
            </div>
        @endif
    </header>

    <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
        <aside class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm space-y-6">
            <div class="space-y-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Tema') }}</h2>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Color primario') }}
                    <input type="color" wire:model.defer="settings.theme.primary" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Color secundario') }}
                    <input type="color" wire:model.defer="settings.theme.secondary" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Fondo') }}
                    <input type="color" wire:model.defer="settings.theme.background" class="mt-1 h-10 w-full rounded-2xl border border-slate-200">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Tipografía (CSS)') }}
                    <input type="text"
                           wire:model.defer="settings.theme.font_family"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>

            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Kits disponibles') }}</h2>
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
                            <p class="text-sm text-slate-500">{{ __('Kit: :kit', ['kit' => $block['kit'] ?? __('Custom')]) }}</p>
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
                    @includeWhen($block['type'] === 'featured-products', 'livewire.admin.page-builder.blocks.featured-products-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'gallery', 'livewire.admin.page-builder.blocks.gallery-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'team', 'livewire.admin.page-builder.blocks.team-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'faq', 'livewire.admin.page-builder.blocks.faq-editor', ['index' => $index, 'block' => $block])
                    @includeWhen($block['type'] === 'timeline', 'livewire.admin.page-builder.blocks.timeline-editor', ['index' => $index, 'block' => $block])
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white/50 p-6 text-center text-sm text-slate-500">
                    {{ __('Aún no has agregado bloques. Selecciona uno de los kits para comenzar.') }}
                </div>
            @endforelse
        </section>
    </div>
</div>

