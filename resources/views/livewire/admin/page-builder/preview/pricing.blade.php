@php($props = $block['props'] ?? [])
@php($items = $props['items'] ?? [])

<section class="rounded-[2rem] border border-slate-100 bg-white px-6 py-8 shadow-sm">
    <div
        contenteditable="true"
        data-inline-edit
        data-placeholder="{{ __('Título del bloque') }}"
        class="text-2xl font-semibold text-slate-900"
        wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.title', $event.target.innerText)">
        {{ $props['title'] ?? __('Planes disponibles') }}
    </div>
    <div class="mt-5 grid gap-4 md:grid-cols-3">
        @foreach($items as $itemIndex => $item)
            @php($featuresText = $item['features_text'] ?? implode(PHP_EOL, $item['features'] ?? []))
            <article class="rounded-2xl border {{ ($item['highlight'] ?? false) ? 'border-indigo-200 bg-indigo-50/70 shadow-md' : 'border-slate-100 bg-white shadow-sm' }} p-4 space-y-3">
                <div
                    contenteditable="true"
                    data-inline-edit
                    data-placeholder="{{ __('Nombre del plan') }}"
                    class="text-lg font-semibold text-slate-900"
                    wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.items.{{ $itemIndex }}.name', $event.target.innerText)">
                    {{ $item['name'] ?? __('Plan') }}
                </div>
                <div class="text-3xl font-bold text-slate-900">
                    <span
                        contenteditable="true"
                        data-inline-edit
                        data-placeholder="99"
                        wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.items.{{ $itemIndex }}.price', $event.target.innerText)">
                        {{ $item['price'] ?? '99' }}
                    </span>
                    <span class="text-base font-semibold text-slate-500"
                          contenteditable="true"
                          data-inline-edit
                          data-placeholder="USD"
                          wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.items.{{ $itemIndex }}.currency', $event.target.innerText)">
                        {{ $item['currency'] ?? 'USD' }}
                    </span>
                </div>
                <div
                    contenteditable="true"
                    data-inline-edit
                    data-placeholder="{{ __('Beneficio principal') }}"
                    class="text-sm text-slate-600"
                    wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.items.{{ $itemIndex }}.cta_label', $event.target.innerText)">
                    {{ $item['cta_label'] ?? __('Elegir') }}
                </div>
                <div
                    contenteditable="true"
                    data-inline-edit
                    data-multiline="true"
                    data-placeholder="{{ __('Característica 1↵Característica 2') }}"
                    class="text-xs text-slate-500"
                    wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.items.{{ $itemIndex }}.features_text', $event.target.innerText)">
                    {{ $featuresText }}
                </div>
            </article>
        @endforeach
    </div>
</section>

