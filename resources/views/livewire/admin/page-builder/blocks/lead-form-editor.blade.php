<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título del formulario') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Descripción') }}
        <textarea wire:model.defer="blocks.{{ $index }}.props.description"
                  rows="2"
                  class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"></textarea>
    </label>
    <div class="space-y-2">
        <p class="text-xs font-semibold text-slate-500">{{ __('Campos') }}</p>
        @foreach($block['props']['fields'] as $fieldIndex => $field)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-3 py-2 space-y-2">
                <input type="text"
                       wire:model.defer="blocks.{{ $index }}.props.fields.{{ $fieldIndex }}.label"
                       class="w-full rounded-xl border border-slate-200 px-3 py-1 text-sm text-slate-700"
                       placeholder="{{ __('Etiqueta') }}">
                <input type="text"
                       wire:model.defer="blocks.{{ $index }}.props.fields.{{ $fieldIndex }}.placeholder"
                       class="w-full rounded-xl border border-slate-200 px-3 py-1 text-xs text-slate-500"
                       placeholder="{{ __('Placeholder') }}">
                <select wire:model.defer="blocks.{{ $index }}.props.fields.{{ $fieldIndex }}.type"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1 text-xs text-slate-500">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="tel">Teléfono</option>
                </select>
            </div>
        @endforeach
    </div>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Etiqueta CTA') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.cta_label"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
</div>

