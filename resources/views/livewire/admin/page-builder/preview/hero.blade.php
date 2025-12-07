@php
    $props = $block['props'] ?? [];
@endphp

<section class="relative overflow-hidden rounded-[2rem] border border-slate-100 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-6 py-10 text-white shadow-inner">
    <div class="space-y-5 max-w-3xl">
        <div
            contenteditable="true"
            data-inline-edit
            data-placeholder="{{ __('Título hero') }}"
            class="text-3xl font-semibold leading-tight tracking-tight"
            wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.headline', $event.target.innerText)">
            {{ $props['headline'] ?? __('Título hero') }}
        </div>
        <div
            contenteditable="true"
            data-inline-edit
            data-placeholder="{{ __('Subtítulo corto para reforzar el mensaje.') }}"
            class="text-base text-slate-200"
            wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.subheadline', $event.target.innerText)">
            {{ $props['subheadline'] ?? __('Subtítulo corto para reforzar el mensaje.') }}
        </div>
        <div class="inline-flex flex-wrap items-center gap-3">
            <span
                contenteditable="true"
                data-inline-edit
                data-placeholder="{{ __('Llamado a la acción') }}"
                class="inline-flex items-center rounded-full bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow"
                wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.cta_label', $event.target.innerText)">
                {{ $props['cta_label'] ?? __('Llamado a la acción') }}
            </span>
            <span class="text-xs text-slate-200">
                {{ $props['cta_url'] ?? 'https://' }}
            </span>
        </div>
    </div>
</section>

