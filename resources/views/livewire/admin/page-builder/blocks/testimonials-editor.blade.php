<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título de la sección') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <div class="space-y-2">
        @foreach($block['props']['items'] as $itemIndex => $item)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-2">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Cita') }}
                    <textarea wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.quote"
                              rows="2"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                </label>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Autor') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.author"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Rol / Cohorte') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.role"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>

