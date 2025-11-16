<div class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6">
        <div class="flex flex-col gap-1">
            <p class="text-xs uppercase text-slate-500 tracking-wide">Programar práctica</p>
            <h3 class="text-lg font-semibold text-slate-900">Sesiones 1:1 / Cohorte en Discord</h3>
            <p class="text-xs text-slate-500">Configura un slot y luego ajusta detalles desde el calendario semanal.</p>
        </div>
        @php
            $weekdayOptions = [
                'monday' => __('Lunes'),
                'tuesday' => __('Martes'),
                'wednesday' => __('Miércoles'),
                'thursday' => __('Jueves'),
                'friday' => __('Viernes'),
                'saturday' => __('Sábado'),
                'sunday' => __('Domingo'),
            ];
        @endphp
        <div class="grid gap-4 md:grid-cols-3">
            <label class="space-y-1 text-xs font-semibold text-slate-500 uppercase tracking-wide" x-data>
                Plantilla guardada
                <select wire:model="selectedTemplateId" @change="$wire.applyTemplate($event.target.value)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Sin plantilla') }}</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs font-semibold text-slate-500 uppercase tracking-wide md:col-span-2">
                Guardar formulario como plantilla
                <div class="flex items-center gap-2">
                    <input type="text" wire:model.defer="templateName" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. Cohorte B2 mañanas">
                    <button type="button" wire:click="saveTemplate" class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:border-indigo-300">
                        💾 Guardar
                    </button>
                    @if($selectedTemplateId)
                        <button type="button" wire:click="deleteTemplate({{ $selectedTemplateId }})" class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:border-rose-300">
                            🗑 Eliminar
                        </button>
                    @endif
                </div>
            </label>
        </div>
        @if(!empty($cohortTemplates))
            <div class="rounded-2xl border border-indigo-100 bg-white/70 p-4 space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs uppercase font-semibold tracking-wide text-indigo-500">Plantillas de cohorte</p>
                        <p class="text-sm text-slate-600">Atajos preconfigurados por programa/horario.</p>
                    </div>
                    @if($selectedCohortTemplate)
                        <button type="button"
                                wire:click="applyCohortTemplate(null)"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-300">
                            ✕ {{ __('Limpiar selección') }}
                        </button>
                    @endif
                    @if(auth()->user()?->hasAnyRole(['Admin', 'teacher_admin']))
                        <a href="{{ route('admin.planner.templates', ['locale' => app()->getLocale()]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white px-3 py-1 text-[11px] font-semibold text-indigo-600 hover:border-indigo-300">
                            ⚙ {{ __('Gestionar presets') }}
                        </a>
                    @endif
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($cohortTemplates as $templateKey => $preset)
                        @php
                            $slotSummary = collect($preset['slots'] ?? [])
                                ->map(fn ($slot) => ($weekdayOptions[$slot['weekday']] ?? ucfirst($slot['weekday'])) . ' · ' . $slot['time'])
                                ->implode(', ');
                            $source = $preset['source'] ?? 'config';
                        @endphp
                        <button type="button"
                                wire:key="cohort-template-{{ $templateKey }}"
                                wire:click="applyCohortTemplate('{{ $templateKey }}')"
                                wire:loading.attr="disabled"
                                wire:target="applyCohortTemplate('{{ $templateKey }}')"
                                class="text-left rounded-2xl border px-4 py-3 text-xs transition {{ $selectedCohortTemplate === $templateKey ? 'border-indigo-400 bg-indigo-50 shadow-inner shadow-indigo-100' : 'border-slate-200 bg-white hover:border-indigo-200' }}">
                            <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                {{ $preset['name'] ?? $templateKey }}
                                @if($selectedCohortTemplate === $templateKey)
                                    <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">{{ __('Activo') }}</span>
                                @endif
                                <span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
                                    {{ $source === 'database' ? __('Equipo') : __('Config') }}
                                </span>
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500">{{ $preset['description'] ?? '—' }}</p>
                            @if($slotSummary)
                                <p class="mt-2 rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] text-slate-600">{{ $slotSummary }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap gap-2 text-[10px] font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                                    ⏱ {{ $preset['duration_minutes'] ?? 60 }} min
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                                    👥 {{ $preset['capacity'] ?? 10 }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4 text-xs text-amber-900 space-y-2">
            <p class="font-semibold">Checklist Make / Discord</p>
            <ul class="list-disc pl-4 space-y-1">
                <li>{{ __('Confirma que el webhook de Discord y el escenario Make usan los eventos `discord.practice.*`.') }}</li>
                <li>{{ __('Cada práctica programada (manual o serie) dispara `DiscordPracticeScheduled` y se registra en el outbox.') }}</li>
                <li>{{ __('No olvides sincronizar el timezone antes de duplicar semanas para evitar desfases en Make.') }}</li>
            </ul>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-xs uppercase font-semibold tracking-wide text-slate-400">Bloques recurrentes</p>
                <button type="button" wire:click="addTemplateSlot" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">
                    ➕ Añadir bloque
                </button>
            </div>
            <p class="text-xs text-slate-500">Define los días y horarios que se repetirán cuando guardes o ejecutes esta plantilla. Ideal para duplicar cohortes completas.</p>
            <div class="space-y-2">
                @foreach($templateSlots as $index => $slot)
                    <div class="grid gap-2 sm:grid-cols-[1fr,1fr,auto] items-center">
                        <label class="text-xs text-slate-500">
                            <span class="sr-only">Día</span>
                            <select wire:model="templateSlots.{{ $index }}.weekday" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($weekdayOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs text-slate-500">
                            <span class="sr-only">Hora</span>
                            <input type="time" wire:model="templateSlots.{{ $index }}.time" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <button type="button"
                                wire:click="removeTemplateSlot({{ $index }})"
                                class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-500 hover:border-rose-200 hover:text-rose-600">
                            ✕
                        </button>
                    </div>
                @endforeach
            </div>
            @error('templateSlots') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold tracking-wide text-indigo-500">Duplicación masiva</p>
                    <p class="text-sm text-slate-600">Programa varias semanas con una sola acción.</p>
                </div>
                <button type="button"
                        wire:click="$set('seriesForm.template_id', {{ $selectedTemplateId ?? 'null' }})"
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white px-3 py-1 text-[11px] font-semibold text-indigo-600 hover:border-indigo-300">
                    Usar plantilla actual
                </button>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Plantilla
                    <select wire:model="seriesForm.template_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Selecciona una plantilla') }}</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('seriesForm.template_id') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Inicio de la serie
                    <input type="date" wire:model="seriesForm.start_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('seriesForm.start_date') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </label>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Semanas
                    <input type="number" min="1" max="12" wire:model="seriesForm.weeks" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('seriesForm.weeks') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </label>
            </div>
            <div class="text-right">
                <button type="button"
                        wire:click="scheduleTemplateSeries"
                        class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    ⚡ Programar serie
                </button>
            </div>
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
        <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/40">
            <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 space-y-3">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase text-slate-400 font-semibold tracking-wide">{{ __('Duplicar semana al futuro') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Replica todos los slots visibles hacia próximas semanas.') }}</p>
                    </div>
                    @error('weekDuplicationForm') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ __('Semana +N') }}
                        <input type="number" min="1" max="12" wire:model.defer="weekDuplicationForm.offset" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ __('Repeticiones') }}
                        <input type="number" min="1" max="6" wire:model.defer="weekDuplicationForm.repeat" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <div class="flex items-end">
                        <button type="button"
                                wire:click="duplicateWeekSeries"
                                class="w-full rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                            {{ __('Duplicar') }}
                        </button>
                    </div>
                </div>
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
                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <button type="button"
                                                                wire:click="duplicatePractice({{ $practice->id }}, 1)"
                                                                class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-500 hover:border-indigo-200 hover:text-indigo-700">
                                                            ⤴︎ +1 día
                                                        </button>
                                                        <button type="button"
                                                                wire:click="duplicatePractice({{ $practice->id }}, 7)"
                                                                class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-500 hover:border-indigo-200 hover:text-indigo-700">
                                                            ⤴︎ +1 semana
                                                        </button>
                                                    </div>
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

