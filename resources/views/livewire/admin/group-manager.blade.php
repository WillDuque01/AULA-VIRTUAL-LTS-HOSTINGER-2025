<div class="mx-auto max-w-6xl space-y-8 px-4 py-12">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400">Teacher Admin</p>
            <h1 class="text-3xl font-semibold text-slate-100">{{ __('Gestion de grupos') }}</h1>
            <p class="text-sm text-slate-400">{{ __('Organiza cohortes, alinea estudiantes con tiers y controla la capacidad de cada grupo.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="createGroup" class="rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-400">{{ __('Nuevo grupo') }}</button>
        </div>
    </div>

    @if (session()->has('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[1.5fr,1fr]">
        <section class="space-y-4">
            @forelse ($groups as $group)
                <article class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-slate-100">{{ $group->name }}</span>
                                <span class="text-xs uppercase tracking-wide text-slate-500">/{{ $group->slug }}</span>
                                @unless($group->is_active)
                                    <span class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-300">{{ __('Inactivo') }}</span>
                                @endunless
                            </div>
                            <p class="text-xs text-slate-500">{{ __('Tier') }}: <strong class="text-slate-200">{{ $group->tier?->name ?? __('Sin asignar') }}</strong></p>
                            @if($group->description)
                                <p class="text-sm text-slate-400">{{ $group->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end text-right text-sm text-slate-400">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Estudiantes activos') }}</span>
                            <span class="text-base font-semibold text-slate-100">{{ $group->active_students_count ?? 0 }}{{ $group->capacity ? ' / '.$group->capacity : '' }}</span>
                            @if($group->starts_at)
                                <span class="text-xs text-slate-500">{{ __('Inicia') }}: {{ optional($group->starts_at)->format('d/m/Y') }}</span>
                            @endif
                            @if($group->ends_at)
                                <span class="text-xs text-slate-500">{{ __('Finaliza') }}: {{ optional($group->ends_at)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" wire:click="editGroup({{ $group->id }})" class="rounded-full bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-400">{{ __('Editar') }}</button>
                        <button type="button" wire:click="openAssignModal({{ $group->id }})" class="rounded-full border border-emerald-500 px-4 py-2 text-sm font-semibold text-emerald-300 hover:bg-emerald-500/10">{{ __('Asignar estudiantes') }}</button>
                        <button type="button" wire:click="toggleActive({{ $group->id }})" class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-slate-500">{{ $group->is_active ? __('Desactivar') : __('Activar') }}</button>
                        <button type="button" wire:click="deleteGroup({{ $group->id }})" class="rounded-full border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10">{{ __('Eliminar') }}</button>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center text-slate-400">
                    {{ __('Aun no hay grupos configurados. Crea el primero para comenzar.') }}
                </div>
            @endforelse
        </section>

        <form wire:submit.prevent="saveGroup" class="flex h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur">
            <h2 class="text-lg font-semibold text-slate-100">{{ $editingId ? __('Editar grupo') : __('Crear nuevo grupo') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Define la informacion clave del grupo y su relacion con los tiers.') }}</p>

            <div class="mt-6 space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-name">{{ __('Nombre') }}</label>
                    <input id="group-name" type="text" wire:model.defer="form.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.name')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-slug">{{ __('Slug') }}</label>
                    <input id="group-slug" type="text" wire:model.defer="form.slug" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="cohort-pro-enero">
                    @error('form.slug')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-tier">{{ __('Tier asociado') }}</label>
                    <select id="group-tier" wire:model.defer="form.tier_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">{{ __('Sin tier') }}</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier['id'] }}">{{ $tier['name'] }} ({{ $tier['slug'] }})</option>
                        @endforeach
                    </select>
                    @error('form.tier_id')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-description">{{ __('Descripcion') }}</label>
                    <textarea id="group-description" rows="3" wire:model.defer="form.description" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="{{ __('Describe objetivo, dinamicas o materiales de la cohorte.') }}"></textarea>
                    @error('form.description')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-capacity">{{ __('Capacidad') }}</label>
                        <input id="group-capacity" type="number" min="1" wire:model.defer="form.capacity" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @error('form.capacity')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-starts">{{ __('Inicio') }}</label>
                        <input id="group-starts" type="date" wire:model.defer="form.starts_at" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @error('form.starts_at')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="group-ends">{{ __('Fin') }}</label>
                        <input id="group-ends" type="date" wire:model.defer="form.ends_at" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @error('form.ends_at')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-6 pt-2 text-sm text-slate-200">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="form.is_active" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500"> {{ __('Activo') }}
                    </label>
                </div>
            </div>

            <div class="mt-auto flex items-center justify-end gap-3 pt-6">
                <button type="button" wire:click="createGroup" class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-slate-500">{{ __('Cancelar') }}</button>
                <button type="submit" class="rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-400">{{ __('Guardar cambios') }}</button>
            </div>
        </form>
    </div>

    @if($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 backdrop-blur-sm">
            <div class="w-full max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/90 p-6 shadow-2xl">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-100">{{ __('Asignar estudiantes al grupo') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Filtra por nombre o correo y marca quienes deben pertenecer a esta cohorte.') }}</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-200" wire:click="closeAssignModal">✕</button>
                </div>

                <div class="mb-4">
                    <input type="text" wire:model.debounce.400ms="studentSearch" placeholder="{{ __('Buscar estudiante...') }}" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>

                <div class="max-h-80 overflow-y-auto rounded-xl border border-slate-800">
                    <table class="w-full text-left text-sm text-slate-200">
                        <tbody>
                            @foreach($students as $student)
                                <tr class="border-b border-slate-800/60 bg-slate-900/60 hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        <label class="flex items-center gap-3">
                                            <input type="checkbox" value="{{ $student->id }}" wire:click="toggleStudent({{ $student->id }})" @checked(in_array($student->id, $selectedStudents, true)) class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500">
                                            <span>
                                                <span class="block text-sm font-semibold">{{ $student->name }}</span>
                                                <span class="block text-xs text-slate-400">{{ $student->email }}</span>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ __('Seleccionados') }}: {{ count($selectedStudents) }}</span>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="closeAssignModal" class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-slate-500">{{ __('Cancelar') }}</button>
                        <button type="button" wire:click="assignSelected" class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-400">{{ __('Guardar asignaciones') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
