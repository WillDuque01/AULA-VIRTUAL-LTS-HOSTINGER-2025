<div class="space-y-5">
    <div class="rounded-3xl border border-slate-100 bg-gradient-to-br from-white to-slate-50 p-5 shadow-inner space-y-3">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Edición inline') }}</p>
        <div contenteditable="true"
             class="text-2xl font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-300 rounded-xl bg-white/60 px-3 py-2"
             wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.headline', $event.target.innerText)">
            {{ $block['props']['headline'] ?? __('Título hero') }}
        </div>
        <div contenteditable="true"
             class="text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-200 rounded-xl bg-white/60 px-3 py-2"
             wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.subheadline', $event.target.innerText)">
            {{ $block['props']['subheadline'] ?? __('Subtítulo corto para reforzar el mensaje.') }}
        </div>
        <div class="inline-flex flex-wrap items-center gap-2">
            <span contenteditable="true"
                  class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-slate-500"
                  wire:input.debounce.500ms="inlineUpdate({{ $index }}, 'props.cta_label', $event.target.innerText)">
                {{ $block['props']['cta_label'] ?? __('Llamado a la acción') }}
            </span>
            <input type="text"
                   wire:model.defer="blocks.{{ $index }}.props.cta_url"
                   placeholder="https://"
                   class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600 focus:border-slate-500 focus:ring-slate-500" />
        </div>
    </div>

    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Imagen (URL opcional)') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.image"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
</div>

