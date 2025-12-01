<div class="space-y-4 rounded-3xl border border-slate-100 bg-white/85 pb-6 shadow-xl shadow-slate-200/60" x-data="{ filtersOpen: window.innerWidth >= 1024 }" x-init="filtersOpen = window.innerWidth >= 1024" x-on:resize.window="filtersOpen = window.innerWidth >= 1024"> {{-- // [AGENTE: GPT-5.1 CODEX] - Controla los filtros responsivos --}}
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('Prácticas en vivo') }}</p>
            <h4 class="text-2xl font-semibold text-slate-900 leading-tight">{{ __('Reserva tu sesión en Discord') }}</h4>
            <p class="text-sm text-slate-500">{{ __('Escoge la práctica ideal para tu curso y confirma cupo en segundos.') }}</p>
        </div>
        <button type="button"
                wire:click="requestSlot"
                class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:text-blue-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200">
            {{ __('Pedir más fechas') }}
        </button>
    </div>
    @if($packReminder)
        <div class="px-6 py-4 border-t border-b border-amber-100 bg-amber-50/80 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-amber-600 tracking-wide">Pack recomendado</p>
                <p class="text-sm text-slate-800 font-semibold">
                    {{ $packReminder['practice_title'] }}
                    @if($packReminder['start_at'])
                        · {{ $packReminder['start_at']->translatedFormat('d M H:i') }}
                    @endif
                </p>
                <p class="text-xs text-slate-600">
                    {{ $packReminder['pack']['title'] }} · {{ $packReminder['pack']['sessions'] }} {{ __('sesiones') }}
                    @if($packReminder['pack']['price_amount'])
                        · ${{ number_format($packReminder['pack']['price_amount'], 0) }} {{ $packReminder['pack']['currency'] }}
                    @endif
                    @if($packReminder['pack']['price_per_session'])
                        (≈ ${{ number_format($packReminder['pack']['price_per_session'], 1) }}/sesión)
                    @endif
                </p>
                <p class="text-[11px] text-amber-700 font-semibold mt-1">
                    {{ $packReminder['pack']['requires_package']
                        ? __('Necesitas un pack activo para reservar este slot.')
                        : __('Activa tu pack para tener prioridad permanente en la agenda.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($packReminder['packs_url'])
                    <a href="{{ $packReminder['packs_url'] }}"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        {{ __('Ver packs') }} ↗
                    </a>
                @endif
                <button type="button"
                        wire:click="dismissPackReminder"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('Descartar') }}
                </button>
            </div>
        </div>
    @endif
    <div class="px-6 py-5 space-y-4">
        <div class="grid gap-3 lg:grid-cols-[2fr,1fr]">
            <div class="space-y-1 text-sm text-slate-600">
                <div class="flex items-center justify-between text-xs uppercase font-semibold tracking-wide text-slate-400">
                    <span>{{ __('Lección') }}</span>
                    <button type="button"
                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 lg:hidden"
                            x-on:click="filtersOpen = !filtersOpen">
                        {{ __('Filtros') }} <span x-text="filtersOpen ? '✕' : '☰'"></span>
                    </button>
                </div>
                <div x-cloak x-show="filtersOpen" class="space-y-1">
                    <select wire:model="selectedLesson" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">{{ __('Todas las lecciones con práctica') }}</option>
                        @foreach($availableLessons as $lesson)
                            <option value="{{ $lesson->id }}">
                                {{ data_get($lesson->chapter?->course, 'slug') }} · {{ data_get($lesson->config, 'title', __('Lesson')) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="flex items-end">
                        <button type="button"
                                wire:click="resetFilters"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-700">
                            {{ __('Limpiar filtros') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid gap-4 lg:grid-cols-3 md:grid-cols-2">
            @forelse($practices as $practice)
                <div class="rounded-2xl border border-slate-100 bg-white/90 p-4 shadow hover:shadow-lg transition">
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
                            {{ __('Cupos: :count', ['count' => $practice['available']]) }}
                        </span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs">
                        @if(isset($statusMessages[$practice['id']]))
                            <p class="font-semibold text-emerald-600">{{ $statusMessages[$practice['id']] }}</p>
                        @endif
                        @if($practice['has_reservation'])
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    ✔ {{ __('Reserva confirmada') }}
                                </span>
                                @if($practice['discord_channel_url'])
                                    <a href="{{ $practice['discord_channel_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-600 hover:border-blue-300">
                                        {{ __('Abrir canal') }} ↗
                                    </a>
                                @endif
                                @if($practice['can_cancel'])
                                    <button type="button"
                                            wire:click="cancelReservation({{ $practice['id'] }})"
                                            class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:border-rose-300">
                                        {{ __('Cancelar reserva') }}
                                    </button>
                                @endif
                            </div>
                        @elseif($practice['available'] > 0)
                            @if($practice['requires_package'] && ! $practice['has_required_pack'])
                                <p class="text-[11px] text-amber-600 font-semibold">{{ __('Necesitas un pack activo para reservar este slot.') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $practice['pack_url'] ?? $packsUrl }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
                                        {{ __('Activar pack') }} ↗
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
                                        {{ __('Reservar cupo') }}
                                    </button>
                                    @if($practice['requires_package'])
                                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                            {{ __('Pack activo detectado') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <button type="button"
                                    wire:click="requestSlot({{ $practice['lesson_id'] }})"
                                    class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:text-blue-600">
                                {{ __('Lista de espera') }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-200 py-6 text-center text-sm text-slate-500">
                    {{ __('No hay sesiones programadas. Solicita una nueva para que tu profesor lo vea.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

