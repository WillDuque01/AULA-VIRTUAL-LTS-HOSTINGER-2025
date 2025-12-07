@php
    $props = $block['props'] ?? [];
@endphp

<section class="rounded-[2rem] border border-slate-100 bg-gradient-to-br from-indigo-50 via-white to-slate-50 px-6 py-8 shadow-inner">
    <div class="space-y-4 text-slate-900">
        <div
            contenteditable="true"
            data-inline-edit
            data-placeholder="{{ __('Título del bloque') }}"
            class="text-2xl font-semibold"
            wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.title', $event.target.innerText)">
            {{ $props['title'] ?? __('Activa tu programa hoy') }}
        </div>
        <div
            contenteditable="true"
            data-inline-edit
            data-placeholder="{{ __('Descripción corta') }}"
            class="text-sm text-slate-600"
            wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.description', $event.target.innerText)">
            {{ $props['description'] ?? __('Agenda una llamada o inscríbete directamente.') }}
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span
                contenteditable="true"
                data-inline-edit
                data-placeholder="{{ __('CTA principal') }}"
                class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.primary_label', $event.target.innerText)">
                {{ $props['primary_label'] ?? __('Inscribirme') }}
            </span>
            <span
                contenteditable="true"
                data-inline-edit
                data-placeholder="{{ __('CTA secundaria') }}"
                class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.secondary_label', $event.target.innerText)">
                {{ $props['secondary_label'] ?? __('Hablar con un advisor') }}
            </span>
        </div>
    </div>
</section>

