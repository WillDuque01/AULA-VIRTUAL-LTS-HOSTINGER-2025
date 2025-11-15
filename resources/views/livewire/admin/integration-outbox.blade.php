<div class="space-y-6" wire:key="integration-outbox">
    <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase font-semibold tracking-[0.3em] text-slate-400">{{ __('outbox.title') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('outbox.subtitle') }}</h2>
        </div>
        <div class="flex flex-wrap gap-3 items-center">
            <select wire:model.live="status"
                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="target"
                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                @foreach($targets as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="search"
                   wire:model.debounce.400ms="search"
                   class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500"
                   placeholder="{{ __('outbox.search_placeholder') }}">
        </div>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('outbox.table.event') }}</th>
                        <th class="px-4 py-3">{{ __('outbox.table.target') }}</th>
                        <th class="px-4 py-3">{{ __('outbox.table.status') }}</th>
                        <th class="px-4 py-3">{{ __('outbox.table.attempts') }}</th>
                        <th class="px-4 py-3">{{ __('outbox.table.last_error') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('outbox.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $event->event }}</p>
                                <p class="text-xs text-slate-500">{{ $event->created_at?->diffForHumans() }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $event->target }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold',
                                    'bg-amber-100 text-amber-800' => $event->status === 'pending',
                                    'bg-emerald-100 text-emerald-700' => $event->status === 'sent',
                                    'bg-rose-100 text-rose-700' => $event->status === 'failed',
                                ])>
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $event->attempts }}</td>
                            <td class="px-4 py-3">
                                @if($event->error_message)
                                    <span class="text-xs text-rose-600">{{ Str::limit($event->error_message, 60) }}</span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="retry({{ $event->id }})"
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-700">
                                    <span aria-hidden="true">⟳</span> {{ __('outbox.actions.retry') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500 text-sm">
                                {{ __('outbox.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $events->links() }}
        </div>
    </section>
</div>

