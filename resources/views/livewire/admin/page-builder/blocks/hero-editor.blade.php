<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título principal') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.headline"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Subtítulo') }}
        <textarea wire:model.defer="blocks.{{ $index }}.props.subheadline"
                  rows="2"
                  class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"></textarea>
    </label>
    <div class="grid gap-3 md:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('Etiqueta CTA') }}
            <input type="text"
                   wire:model.defer="blocks.{{ $index }}.props.cta_label"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('URL CTA') }}
            <input type="text"
                   wire:model.defer="blocks.{{ $index }}.props.cta_url"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
    </div>
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Imagen (URL opcional)') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.image"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
</div>

