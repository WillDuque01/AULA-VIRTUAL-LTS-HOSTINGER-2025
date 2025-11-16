<div class="space-y-4">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título de la sección') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <div class="space-y-3">
        @foreach($block['props']['items'] as $itemIndex => $item)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-2">
                <div class="flex items-center justify-between text-sm font-semibold text-slate-600">
                    <span>{{ __('Plan :n', ['n' => $itemIndex + 1]) }}</span>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Nombre') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.name"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                    </label>
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Precio') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.price"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                    </label>
                </div>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('CTA label') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.cta_label"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('CTA URL') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.cta_url"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Features (una por línea)') }}
                    <textarea wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.features_text"
                              rows="2"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <input type="checkbox"
                           wire:model.defer="blocks.{{ $index }}.props.items.{{ $itemIndex }}.highlight"
                           class="rounded border-slate-300">
                    {{ __('Destacar plan') }}
                </label>
            </div>
        @endforeach
    </div>
</div>

