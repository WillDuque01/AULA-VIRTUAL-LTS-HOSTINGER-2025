<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título de la galería') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <div class="space-y-2">
        @foreach($block['props']['items'] as $itemIndex => $item)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-2">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Imagen (URL)') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.image"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Descripción') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.caption"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>
        @endforeach
    </div>
</div>

