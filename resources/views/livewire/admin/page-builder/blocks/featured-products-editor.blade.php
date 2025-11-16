<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título de la sección') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Máximo de productos a mostrar') }}
        <input type="number"
               min="1"
               wire:model.defer="blocks.{{ $index }}.props.max_items"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
        <input type="checkbox"
               wire:model.defer="blocks.{{ $index }}.props.show_badges"
               class="rounded border-slate-300">
        {{ __('Mostrar badges de estado/precio') }}
    </label>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Filtrar por categoría (opcional)') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.category"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
               placeholder="{{ __('Ej. packs, cohortes') }}">
    </label>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('IDs específicos (separados por coma)') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.product_ids_text"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
               placeholder="12,45,78">
        <span class="text-xs text-slate-500">{{ __('Si agregas IDs, se ignoran el filtro por categoría y destacados automáticos.') }}</span>
    </label>
</div>

