<div class="space-y-3">
    <label class="block text-sm font-semibold text-slate-700">
        {{ __('Título del equipo') }}
        <input type="text"
               wire:model.defer="blocks.{{ $index }}.props.title"
               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
    </label>
    <div class="space-y-2">
        @foreach($block['props']['members'] as $itemIndex => $member)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 space-y-2">
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Nombre') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.members.{{ $itemIndex }}.name"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-semibold text-slate-600">
                        {{ __('Rol') }}
                        <input type="text"
                               wire:model.defer="blocks.{{ $index }}.props.members.{{ $itemIndex }}.role"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    </label>
                </div>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Avatar (URL)') }}
                    <input type="text"
                           wire:model.defer="blocks.{{ $index }}.props.members.{{ $itemIndex }}.avatar"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Bio') }}
                    <textarea wire:model.defer="blocks.{{ $index }}.props.members.{{ $itemIndex }}.bio"
                              rows="2"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                </label>
            </div>
        @endforeach
    </div>
</div>

