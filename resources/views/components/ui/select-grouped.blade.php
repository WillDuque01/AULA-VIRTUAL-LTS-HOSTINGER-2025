@props([
    'groups' => [],
    'placeholder' => __('Seleccionar...'),
]) {{-- // [AGENTE: GPT-5.1 CODEX] - Define propiedades del componente select agrupado --}}

@php
    $wireModel = $attributes->wire('model'); // [AGENTE: GPT-5.1 CODEX] - Referencia a wire:model para usar entangle
    $flatOptions = collect($groups)
        ->flatMap(fn ($items) => $items)
        ->toArray(); // [AGENTE: GPT-5.1 CODEX] - Mapa id => etiqueta para recuperar el label seleccionado
@endphp

<div
    {{ $attributes->class('relative') }}
    x-data="{
        open: false,
        selected: @if($wireModel) @entangle($wireModel).live @else null @endif,
        options: @js($flatOptions),
        placeholder: '{{ $placeholder }}',
        selectedLabel() {
            return this.selected ? (this.options[this.selected] ?? this.placeholder) : this.placeholder;
        },
        select(value) {
            this.selected = value;
            this.open = false;
        }
    }"
    x-on:keydown.escape.prevent.stop="open = false"
    x-cloak
>
    <button
        type="button"
        class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-100"
        x-on:click="open = !open"
    > {{-- // [AGENTE: GPT-5.1 CODEX] - Botón disparador del dropdown --}}
        <span class="truncate" x-text="selectedLabel()"></span>
        <span class="text-slate-400">▼</span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        @click.outside="open = false"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-slate-100 bg-white/95 py-1 shadow-2xl shadow-slate-200/70 ring-1 ring-black/5"
    > {{-- // [AGENTE: GPT-5.1 CODEX] - Panel flotante con grupos --}}
        @foreach($groups as $group => $items)
            <div class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400 bg-slate-50/70">
                {{ $group }}
            </div>
            @foreach($items as $value => $label)
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-between px-4 py-2 text-sm text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700"
                    :class="{ 'text-indigo-700 font-semibold': selected == '{{ $value }}' }"
                    x-on:click="select('{{ $value }}')"
                > {{-- // [AGENTE: GPT-5.1 CODEX] - Opción individual --}}
                    <span>{{ $label }}</span>
                    <span x-show="selected == '{{ $value }}'">✓</span>
                </button>
            @endforeach
        @endforeach
    </div>
</div>

