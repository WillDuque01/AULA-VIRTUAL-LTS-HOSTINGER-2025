<div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Prácticas en vivo</p>
            <h4 class="text-lg font-semibold text-slate-900">Reserva tu sesión en Discord</h4>
        </div>
        <button type="button"
                wire:click="requestSlot"
                class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
            Pedir más fechas
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
        <label class="space-y-1 text-sm text-slate-600">
            <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Lección</span>
            <select wire:model="selectedLesson" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('Todas las lecciones con práctica') }}</option>
                @foreach($availableLessons as $lesson)
                    <option value="{{ $lesson->id }}">
                        {{ data_get($lesson->chapter?->course, 'slug') }} · {{ data_get($lesson->config, 'title', __('Lesson')) }}
                    </option>
                @endforeach
            </select>
        </label>
        <div class="divide-y divide-slate-100">
            @forelse($practices as $practice)
                <div class="py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $practice['title'] }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $practice['course'] }} · {{ $practice['lesson'] }} · {{ $practice['start_at']->format('d M H:i') }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ __('Cupos disponibles: :count', ['count' => $practice['available']]) }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 text-sm">
                        @if($practice['has_reservation'])
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                ✔ {{ __('Reserva confirmada') }}
                            </span>
                            @if($practice['discord_channel_url'])
                                <a href="{{ $practice['discord_channel_url'] }}" target="_blank" rel="noopener" class="text-xs font-semibold text-blue-600 hover:underline">
                                    Abrir canal
                                </a>
                            @endif
                        @elseif($practice['available'] > 0)
                            @if($practice['requires_package'] && ! $practice['has_required_pack'])
                                <p class="text-[11px] text-amber-600 font-semibold">{{ __('Necesitas un pack activo para reservar este slot.') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $packsUrl }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
                                        {{ __('Activar pack') }} ↗
                                    </a>
                                </div>
                            @else
                                <button type="button"
                                        wire:click="reserve({{ $practice['id'] }})"
                                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                    Reservar cupo
                                </button>
                                @if($practice['requires_package'])
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                        {{ __('Pack activo detectado') }}
                                    </span>
                                @endif
                            @endif
                        @else
                            <button type="button"
                                    wire:click="requestSlot({{ $practice['lesson_id'] }})"
                                    class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
                                Lista de espera
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-slate-500">
                    {{ __('No hay sesiones programadas. Solicita una nueva para que tu profesor lo vea.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

