<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título') }}
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
    <div class="grid gap-3 md:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('CTA principal') }}
            <input type="text" wire:model.defer="blocks.{{ $index }}.props.primary_label"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('URL CTA principal') }}
            <input type="text" wire:model.defer="blocks.{{ $index }}.props.primary_url"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
    </div>
    <div class="grid gap-3 md:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('CTA secundaria') }}
            <input type="text" wire:model.defer="blocks.{{ $index }}.props.secondary_label"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
        <label class="block text-sm font-semibold text-slate-700">
            {{ __('URL CTA secundaria') }}
            <input type="text" wire:model.defer="blocks.{{ $index }}.props.secondary_url"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
        </label>
    </div>
</div>

