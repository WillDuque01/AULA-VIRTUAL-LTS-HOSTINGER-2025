<div class="space-y-4 rounded-3xl border border-slate-100 bg-white/85 pb-6 shadow-xl shadow-slate-200/60" x-data="{ filtersOpen: window.innerWidth >= 1024 }" x-init="filtersOpen = window.innerWidth >= 1024" x-on:resize.window="filtersOpen = window.innerWidth >= 1024"> {{-- // [AGENTE: GPT-5.1 CODEX] - Controla los filtros responsivos --}}
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('student.browser.title') }}</p>
            <h4 class="text-2xl font-semibold text-slate-900 leading-tight">{{ __('student.browser.subtitle') }}</h4>
            <p class="text-sm text-slate-500">{{ __('student.browser.description') }}</p>
        </div>
        <button type="button"
                wire:click="requestSlot"
                wire:loading.attr="disabled"
                wire:target="requestSlot"
                class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:text-blue-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200 disabled:opacity-60">
            <span wire:loading wire:target="requestSlot" class="animate-spin text-xs">⏳</span>
            {{ __('student.browser.request_dates') }}
        </button>
    </div>
    @if($packReminder)
        <div class="px-6 py-4 border-t border-b border-amber-100 bg-amber-50/80 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-amber-600 tracking-wide">{{ __('student.browser.recommended_pack') }}</p>
                <p class="text-sm text-slate-800 font-semibold">
                    {{ $packReminder['practice_title'] }}
                    @if($packReminder['start_at'])
                        · {{ $packReminder['start_at']->translatedFormat('d M H:i') }}
                    @endif
                </p>
                <p class="text-xs text-slate-600">
                    {{ $packReminder['pack']['title'] }} · {{ $packReminder['pack']['sessions'] }} {{ __('student.browser.sessions') }}
                    @if($packReminder['pack']['price_amount'])
                        · ${{ number_format($packReminder['pack']['price_amount'], 0) }} {{ $packReminder['pack']['currency'] }}
                    @endif
                    @if($packReminder['pack']['price_per_session'])
                        (≈ ${{ number_format($packReminder['pack']['price_per_session'], 1) }}/sesión)
                    @endif
                </p>
                <p class="text-[11px] text-amber-700 font-semibold mt-1">
                    {{ $packReminder['pack']['requires_package']
                        ? __('student.browser.requires_pack')
                        : __('student.browser.activate_pack_hint') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($packReminder['packs_url'])
                    <a href="{{ $packReminder['packs_url'] }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        {{ __('student.browser.view_packs') }} ↗
                    </a>
                @endif
                <button type="button"
                        wire:click="dismissPackReminder"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('student.browser.dismiss') }}
                </button>
            </div>
        </div>
    @endif
    <div class="px-6 py-5 space-y-4">
        <div class="grid gap-3 lg:grid-cols-[2fr,1fr]">
            <div class="space-y-1 text-sm text-slate-600">
                <div class="flex items-center justify-between text-xs uppercase font-semibold tracking-wide text-slate-400">
                    <span>{{ __('student.browser.lesson_label') }}</span>
                    <button type="button"
                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 lg:hidden"
                            x-on:click="filtersOpen = !filtersOpen">
                        {{ __('student.browser.filters') }} <span x-text="filtersOpen ? '✕' : '☰'"></span>
                    </button>
                </div>
                <div x-cloak x-show="filtersOpen" class="space-y-2">
                    <x-ui.select-grouped
                        wire:model="selectedLesson"
                        :groups="$lessonGroups"
                        :placeholder="__('student.browser.all_lessons')"
                        class="w-full"
                    /> {{-- // [AGENTE: GPT-5.1 CODEX] - Selector agrupado por curso --}}
                    <div class="flex items-end">
                        <button type="button"
                                wire:click="resetFilters"
                                wire:loading.attr="disabled"
                                wire:target="resetFilters"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-700 disabled:opacity-60">
                            <span wire:loading wire:target="resetFilters" class="animate-spin text-xs">⏳</span>
                            {{ __('student.browser.reset_filters') }}
                        </button>
                    </div>
                    @error('request')
                        <p class="text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <div class="grid gap-4 lg:grid-cols-3 md:grid-cols-2">
            @forelse($practices as $practice)
                <div class="rounded-2xl border border-slate-100 bg-white/90 p-4 shadow hover:shadow-lg transition" wire:key="practice-{{ $practice['id'] }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $practice['title'] }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $practice['course'] }} · {{ $practice['lesson'] }} · {{ $practice['start_at']->format('d M H:i') }}
                            </p>
                        </div>
                        <span @class([
                            'rounded-full px-3 py-1 text-[11px] font-semibold',
                            'border border-emerald-200 bg-emerald-50 text-emerald-700' => $practice['available'] > 0,
                            'border border-slate-200 bg-slate-100 text-slate-500' => $practice['available'] <= 0,
                        ])>
                            {{ __('student.browser.spots', ['count' => $practice['available']]) }}
                        </span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs">
                        @if(isset($statusMessages[$practice['id']]))
                            <p class="font-semibold text-emerald-600">{{ $statusMessages[$practice['id']] }}</p>
                        @endif
                        @if($practice['has_reservation'])
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    ✔ {{ __('student.browser.reservation_confirmed') }}
                                </span>
                                @if($practice['discord_channel_url'])
                                    <a href="{{ $practice['discord_channel_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-600 hover:border-blue-300">
                                        {{ __('student.browser.open_channel') }} ↗
                                    </a>
                                @endif
                                @if($practice['can_cancel'])
                                    <button type="button"
                                            wire:click="cancelReservation({{ $practice['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="cancelReservation({{ $practice['id'] }})"
                                            class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:border-rose-300 disabled:opacity-60">
                                        <span wire:loading wire:target="cancelReservation({{ $practice['id'] }})" class="animate-spin text-xs">⏳</span>
                                        {{ __('student.browser.cancel_reservation') }}
                                    </button>
                                @endif
                            </div>
                        @elseif($practice['available'] > 0)
                            @if($practice['requires_package'] && ! $practice['has_required_pack'])
                                <p class="text-[11px] text-amber-600 font-semibold">{{ __('student.browser.need_pack') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $practice['pack_url'] ?? $packsUrl }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
                                        {{ __('student.browser.activate_pack') }} ↗
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            wire:click="reserve({{ $practice['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="reserve({{ $practice['id'] }})"
                                            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800 disabled:opacity-70">
                                        <span wire:loading wire:target="reserve({{ $practice['id'] }})" class="animate-spin text-xs">⏳</span>
                                        {{ __('student.browser.reserve_spot') }}
                                    </button>
                                    @if($practice['requires_package'])
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                            {{ __('student.browser.pack_detected') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <button type="button"
                                    wire:click="requestSlot({{ $practice['lesson_id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="requestSlot({{ $practice['lesson_id'] }})"
                                    class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:text-blue-600 disabled:opacity-60">
                                <span wire:loading wire:target="requestSlot({{ $practice['lesson_id'] }})" class="animate-spin text-xs">⏳</span>
                                {{ __('student.browser.waitlist') }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-500">
                    {{ __('student.browser.empty_state') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

