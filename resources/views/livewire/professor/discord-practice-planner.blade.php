<div class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div class="flex flex-col gap-1">
            <p class="text-xs uppercase text-slate-500 tracking-wide">Programar práctica</p>
            <h3 class="text-lg font-semibold text-slate-900">Sesiones 1:1 / Cohorte en Discord</h3>
            <p class="text-xs text-slate-500">Configura un slot y luego ajusta detalles desde el calendario semanal.</p>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <label class="space-y-1 text-sm text-slate-600">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Lección</span>
                <select wire:model="selectedLesson" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('Selecciona una lección') }}</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}">
                            {{ data_get($lesson->chapter?->course, 'slug') }} · {{ data_get($lesson->config, 'title') ?? __('Lesson :pos', ['pos' => $lesson->position]) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-sm text-slate-600">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Título visible</span>
                <input type="text" wire:model.defer="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Práctica de conversación B2">
            </label>
            <label class="space-y-1 text-sm text-slate-600 md:col-span-2">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Descripción / objetivos</span>
                <textarea wire:model.defer="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Repasaremos comandos clave y pronunciación en vivo."></textarea>
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Modalidad</span>
                    <select wire:model="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="cohort">Cohorte específica</option>
                        <option value="global">Todos los estudiantes</option>
                    </select>
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Cohorte / grupo (opcional)</span>
                    <input type="text" wire:model.defer="cohort_label" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. Cohorte B2-12">
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" wire:model="requires_package" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                    <span>Requiere pack activo</span>
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Pack asociado</span>
                    <select wire:model="practice_package_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" @disabled(!$requires_package)>
                        <option value="">{{ __('Opcional') }}</option>
                        @foreach($packages as $pack)
                            <option value="{{ $pack->id }}">{{ $pack->title }} · {{ $pack->sessions_count }} sesiones</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Inicio</span>
                    <input type="datetime-local" wire:model.defer="start_at" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Duración (min)</span>
                    <input type="number" wire:model.defer="duration_minutes" min="15" max="240" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Capacidad</span>
                    <input type="number" wire:model.defer="capacity" min="1" max="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Canal Discord</span>
                    <input type="url" wire:model.defer="discord_channel_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://discord.gg/...">
                </label>
            </div>
            <div class="md:col-span-2 flex items-center justify-end gap-3">
                <button type="button" wire:click="createPractice" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Programar sesión
                </button>
            </div>
        </div>
    </div>

    @php
        $weekStart = \Illuminate\Support\Carbon::parse($calendarRangeStart);
        $weekEnd = \Illuminate\Support\Carbon::parse($calendarRangeEnd);
        $practicesBySlot = $practices->groupBy(fn ($practice) => optional($practice->start_at)->format('Y-m-d H:i'));
    @endphp

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase text-slate-500 tracking-wide">Calendario semanal</p>
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M') }}
                </h3>
                <p class="text-xs text-slate-500">Arrastra los bloques a otra hora o día para reorganizar con un gesto.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                <button wire:click="goToPreviousWeek" class="rounded-full border border-slate-200 px-3 py-1 hover:border-blue-300 hover:text-blue-600">← Semana anterior</button>
                <button wire:click="resetWeek" class="rounded-full border border-slate-200 px-3 py-1 hover:border-blue-300 hover:text-blue-600">Semana actual</button>
                <button wire:click="goToNextWeek" class="rounded-full border border-slate-200 px-3 py-1 hover:border-blue-300 hover:text-blue-600">Semana siguiente →</button>
            </div>
        </div>
        <div class="px-4 pb-6 overflow-x-auto">
            <div
                x-data="{
                    dragged: null,
                    startDrag(id) { this.dragged = id },
                    drop(date, hour) {
                        if (!this.dragged) return;
                        $wire.movePractice(this.dragged, date, hour);
                        this.dragged = null;
                    },
                    clear() { this.dragged = null; }
                }"
            >
                <div class="min-w-[960px]">
                    <div class="grid grid-cols-8 gap-3 px-2 py-3 text-xs font-semibold text-slate-500">
                        <div class="text-right pr-2">Hora</div>
                        @foreach($this->calendarDays as $day)
                            <div class="text-center">{{ $day['date']->translatedFormat('D d') }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-8 gap-3 px-2 pb-4">
                        <div class="space-y-4 text-right pr-2 text-xs text-slate-500">
                            @foreach($calendarHours as $hour)
                                <div class="h-20 flex items-start justify-end">{{ $hour }}</div>
                            @endforeach
                        </div>
                        @foreach($this->calendarDays as $day)
                            <div class="space-y-4">
                                @foreach($calendarHours as $hour)
                                    @php($slotKey = $day['date']->format('Y-m-d').' '.$hour)
                                    <div
                                        class="relative h-20 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-2"
                                        @dragover.prevent
                                        @drop.prevent="drop('{{ $day['date']->toDateString() }}', '{{ $hour }}')"
                                    >
                                        <span class="absolute right-2 top-1 text-[10px] uppercase text-slate-300">{{ $hour }}</span>
                                        <div class="space-y-2">
                                            @foreach($practicesBySlot[$slotKey] ?? [] as $practice)
                                                <div
                                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm cursor-move transition hover:border-blue-300"
                                                    draggable="true"
                                                    @dragstart="startDrag({{ $practice->id }})"
                                                    @dragend="clear()"
                                                >
                                                    <p class="font-semibold text-slate-800 text-sm">{{ $practice->title }}</p>
                                                    <p class="text-[11px] text-slate-500">
                                                        {{ data_get($practice->lesson?->chapter?->course, 'slug') }}
                                                        · {{ data_get($practice->lesson?->config, 'title', __('Lección')) }}
                                                    </p>
                                                    <p class="text-[11px] text-slate-400 flex items-center justify-between">
                                                        <span>{{ __('Cupos') }} {{ $practice->reservations->count() }} / {{ $practice->capacity }}</span>
                                                        <span class="capitalize">{{ $practice->type }}</span>
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

