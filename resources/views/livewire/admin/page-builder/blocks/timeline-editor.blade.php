<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título del timeline') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <div class="space-y-2">
        @foreach($block['props']['steps'] as $itemIndex => $step)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-2">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Título') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.steps.{{ $itemIndex }}.title"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Descripción') }}
                    <textarea wire:model.defer="blocks.{{ $index }}.props.steps.{{ $itemIndex }}.description"
                              rows="2"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Badge') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.steps.{{ $itemIndex }}.badge"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>
        @endforeach
    </div>
</div>

