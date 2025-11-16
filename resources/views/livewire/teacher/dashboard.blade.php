<div class="space-y-8">
    <div class="grid gap-4 md:grid-cols-2">
        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Cursos asignados') }}</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Tu aula operativa') }}</h2>
                </div>
                <button type="button"
                        wire:click="openSubmissionModal('module')"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    {{ __('Proponer módulo') }}
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($courses as $course)
                    <article class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ data_get($course->i18n->first(), 'title', $course->slug) }}</p>
                                <p class="text-xs text-slate-500">{{ trans_choice(':count módulo|:count módulos', $course->chapters->count(), ['count' => $course->chapters->count()]) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        wire:click="openSubmissionModal('lesson')"
                                        wire:key="lesson-btn-{{ $course->id }}"
                                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                                    {{ __('Proponer lección') }}
                                </button>
                                <button type="button"
                                        wire:click="openSubmissionModal('pack')"
                                        wire:key="pack-btn-{{ $course->id }}"
                                        class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                                    {{ __('Proponer pack') }}
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">{{ __('Aún no tienes cursos asignados. Pide a tu Teacher Admin que te agregue a una cohorte.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Estado de propuestas') }}</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Revisiones en curso') }}</h2>
                </div>
                <button type="button"
                        wire:click="loadData"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-500 hover:border-slate-400">
                    {{ __('Actualizar') }}
                </button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($submissions as $submission)
                    @php
                        $contentStatus = optional($submission->result)->status;
                    @endphp
                    <article class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $submission->title }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ ucfirst($submission->type) }} ·
                                    {{ __('Estado: :status', ['status' => __($submission->status)]) }}
                                </p>
                                @if($contentStatus)
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        {{ __('Contenido: :status', ['status' => __($contentStatus)]) }}
                                    </p>
                                @endif
                                @if($submission->feedback)
                                    <p class="mt-1 text-xs text-amber-600">{{ $submission->feedback }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2 text-xs font-semibold">
                                <span class="inline-flex items-center justify-center rounded-full px-3 py-1
                                    @class([
                                        'bg-slate-100 text-slate-700' => $submission->status === 'pending',
                                        'bg-emerald-100 text-emerald-700' => $submission->status === 'approved',
                                        'bg-rose-100 text-rose-700' => $submission->status === 'rejected',
                                    ])
                                ">
                                    {{ $submission->status === 'pending' ? __('Propuesta pendiente') : ($submission->status === 'approved' ? __('Propuesta aprobada') : __('Propuesta rechazada')) }}
                                </span>
                                @if($contentStatus)
                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1
                                        @class([
                                            'bg-amber-100 text-amber-700' => $contentStatus === 'pending',
                                            'bg-emerald-100 text-emerald-700' => $contentStatus === 'published',
                                            'bg-rose-100 text-rose-700' => $contentStatus === 'rejected',
                                        ])
                                    ">
                                        {{ __('Contenido: :status', ['status' => __($contentStatus)]) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">{{ __('Todavía no has enviado propuestas. Usa los botones de arriba para comenzar.') }}</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Atajos rápidos') }}</p>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recursos para docentes') }}</h2>
            </div>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <a href="{{ route('professor.discord-practices', ['locale' => app()->getLocale()]) }}"
               class="rounded-2xl border border-slate-100 px-4 py-3 text-sm text-slate-700 hover:border-slate-300">
                <p class="font-semibold text-slate-900">{{ __('Planner Discord') }}</p>
                <p class="text-xs text-slate-500">{{ __('Consulta el calendario de prácticas aprobadas.') }}</p>
            </a>
            <a href="{{ route('professor.practice-packs', ['locale' => app()->getLocale()]) }}"
               class="rounded-2xl border border-slate-100 px-4 py-3 text-sm text-slate-700 hover:border-slate-300">
                <p class="font-semibold text-slate-900">{{ __('Gestor de Packs') }}</p>
                <p class="text-xs text-slate-500">{{ __('Revisa los packs publicados y su desempeño.') }}</p>
            </a>
            <a href="{{ route('courses.builder', ['locale' => app()->getLocale(), 'course' => optional($courses->first())->id]) }}"
               class="rounded-2xl border border-slate-100 px-4 py-3 text-sm text-slate-700 hover:border-slate-300">
                <p class="font-semibold text-slate-900">{{ __('Course Builder') }}</p>
                <p class="text-xs text-slate-500">{{ __('Acceso rápido al constructor cuando tengas aprobación.') }}</p>
            </a>
        </div>
    </section>

    <x-modal name="teacher-submission" :show="$showSubmissionModal">
        <div class="space-y-4">
            <div>
                <p class="text-xs uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('Nueva propuesta') }}</p>
                <h3 class="text-lg font-semibold text-slate-900">
                    @switch($form['type'])
                        @case('module')
                            {{ __('Proponer un módulo') }}
                            @break
                        @case('pack')
                            {{ __('Proponer un pack') }}
                            @break
                        @default
                            {{ __('Proponer una lección') }}
                    @endswitch
                </h3>
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Curso') }}
                    <select wire:model="form.course_id"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                        <option value="">{{ __('Selecciona un curso') }}</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ data_get($course->i18n->first(), 'title', $course->slug) }}</option>
                        @endforeach
                    </select>
                </label>

                @if($form['type'] === 'lesson')
                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Módulo') }}
                        <select wire:model="form.chapter_id"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                            <option value="">{{ __('Selecciona módulo') }}</option>
                            @foreach($chapters ?? [] as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Título propuesto') }}
                    <input type="text" wire:model.defer="form.title"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900" />
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Resumen / objetivo') }}
                    <textarea wire:model.defer="form.summary"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                              rows="3"></textarea>
                </label>

                @if($form['type'] === 'lesson')
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ __('Tipo de lección') }}
                            <select wire:model.defer="form.lesson_type"
                                    class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                                <option value="video">{{ __('Video') }}</option>
                                <option value="text">{{ __('Texto enriquecido') }}</option>
                                <option value="pdf">{{ __('PDF / recurso') }}</option>
                                <option value="quiz">{{ __('Quiz') }}</option>
                                <option value="assignment">{{ __('Tarea') }}</option>
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ __('Minutos estimados') }}
                            <input type="number" wire:model.defer="form.estimated_minutes" min="1"
                                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900" />
                        </label>
                    </div>
                @elseif($form['type'] === 'pack')
                    <div class="grid gap-3 md:grid-cols-3">
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ __('Sesiones') }}
                            <input type="number" min="1" wire:model.defer="form.pack_sessions"
                                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900" />
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ __('Precio') }}
                            <input type="number" min="0" step="0.01" wire:model.defer="form.pack_price"
                                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900" />
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ __('Moneda') }}
                            <input type="text" wire:model.defer="form.pack_currency" maxlength="3"
                                   class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900 uppercase" />
                        </label>
                    </div>
                @endif

                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Notas adicionales') }}
                    <textarea wire:model.defer="form.notes"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                              rows="3"></textarea>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button"
                        wire:click="$set('showSubmissionModal', false)"
                        class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                    {{ __('Cancelar') }}
                </button>
                <button type="button"
                        wire:click="submitProposal"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Enviar a revisión') }}
                </button>
            </div>
        </div>
    </x-modal>
</div>

