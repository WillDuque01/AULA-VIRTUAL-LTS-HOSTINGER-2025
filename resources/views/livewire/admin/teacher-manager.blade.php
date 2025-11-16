<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Gestión de docentes') }}</p>
                <h1 class="text-xl font-semibold text-slate-900">{{ __('Roles & Cohortes') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Promueve, revoca o asigna cursos a tus Team Teachers.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="selectAll"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('Seleccionar todo') }}
                </button>
                <button type="button" wire:click="clearSelection"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('Limpiar selección') }}
                </button>
            </div>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <button type="button" wire:click="promoteSelected"
                    class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-left">
                <p class="text-sm font-semibold text-indigo-900">{{ __('Promover a Teacher Admin') }}</p>
                <p class="text-xs text-indigo-700">{{ __('Otorga acceso al planner, aprobaciones y gestión de cohortes.') }}</p>
            </button>
            <button type="button" wire:click="demoteSelected"
                    class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-left">
                <p class="text-sm font-semibold text-amber-900">{{ __('Revocar privilegios') }}</p>
                <p class="text-xs text-amber-700">{{ __('Mantiene acceso docente, pero sin aprobación global.') }}</p>
            </button>
            <button type="button" wire:click="removeSelected"
                    class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-left">
                <p class="text-sm font-semibold text-rose-900">{{ __('Eliminar docentes') }}</p>
                <p class="text-xs text-rose-700">{{ __('Quita todos los permisos y desvincula cursos asignados.') }}</p>
            </button>
        </div>
    </header>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <label class="text-sm font-semibold text-slate-700 flex-1">
                {{ __('Buscar docente') }}
                <input type="text" wire:model.debounce.400ms="search"
                       class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                       placeholder="{{ __('Nombre o correo') }}" />
            </label>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
                {{ trans_choice(':count docente seleccionado|:count docentes seleccionados', count($selected), ['count' => count($selected)]) }}
            </p>
        </div>
        <div class="mt-6 space-y-3">
            @forelse($teachers as $teacher)
                <article class="rounded-2xl border border-slate-100 px-4 py-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-start gap-3">
                            <button type="button" wire:click="toggleSelection({{ $teacher->id }})"
                                    class="mt-1 h-5 w-5 rounded-full border border-slate-300 text-xs font-semibold
                                        @class([
                                            'bg-slate-900 text-white' => in_array($teacher->id, $selected, true),
                                            'bg-white text-transparent' => !in_array($teacher->id, $selected, true),
                                        ])">
                                ✓
                            </button>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $teacher->name ?? $teacher->email }}</p>
                                <p class="text-xs text-slate-500">{{ $teacher->email }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold">
                                    @foreach($teacher->roles as $role)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-slate-600">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 md:w-1/2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ __('Cursos asignados') }}
                                <select wire:model.defer="courseAssignments.{{ $teacher->id }}"
                                        wire:change="saveCourseAssignment({{ $teacher->id }})"
                                        multiple
                                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->slug }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <p class="text-[11px] text-slate-500">
                                {{ __('Selecciona uno o varios cursos para que pueda enviar propuestas y actualizar módulos.') }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-500">{{ __('No se encontraron docentes con los filtros actuales.') }}</p>
            @endforelse
        </div>
    </section>
</div>

