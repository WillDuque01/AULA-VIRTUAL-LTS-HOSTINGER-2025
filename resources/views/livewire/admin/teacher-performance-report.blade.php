<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Operación docente') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Reporte de desempeño docente') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Monitorea envíos, tiempos de aprobación y backlog por docente.') }}</p>
            </div>
            <div class="flex gap-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2 text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Promedio aprobación') }}</p>
                    <p class="text-xl font-semibold text-slate-900">
                        {{ $summary['avg_minutes'] ? $summary['avg_minutes'].' min' : '—' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2 text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Pendientes') }}</p>
                    <p class="text-xl font-semibold text-amber-600">{{ $summary['pending'] }}</p>
                </div>
            </div>
        </div>
    </header>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="grid gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Docente') }}
                <select wire:model="filters.teacher_id"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Curso') }}
                <select wire:model="filters.course_id"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->slug }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Estado de propuesta') }}
                <select wire:model="filters.status"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">{{ __('Todos') }}</option>
                    <option value="pending">{{ __('Pendiente') }}</option>
                    <option value="approved">{{ __('Aprobado') }}</option>
                    <option value="rejected">{{ __('Rechazado') }}</option>
                </select>
            </label>
        </div>
        <div class="mt-3 grid gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Tipo') }}
                <select wire:model="filters.type"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">{{ __('Todos') }}</option>
                    <option value="module">{{ __('Módulos') }}</option>
                    <option value="lesson">{{ __('Lecciones') }}</option>
                    <option value="pack">{{ __('Practice packs') }}</option>
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Desde') }}
                <input type="date"
                       wire:model="filters.date_from"
                       class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500" />
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Hasta') }}
                <input type="date"
                       wire:model="filters.date_to"
                       class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500" />
            </label>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-2">{{ __('Docente') }}</th>
                        <th class="px-4 py-2 text-center">{{ __('Pend.') }}</th>
                        <th class="px-4 py-2 text-center">{{ __('Aprob.') }}</th>
                        <th class="px-4 py-2 text-center">{{ __('Rech.') }}</th>
                        <th class="px-4 py-2 text-center">{{ __('Aprobación') }}</th>
                        <th class="px-4 py-2 text-center">{{ __('Avg. min') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Última propuesta') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $row['teacher']?->name ?? __('Docente sin nombre') }}</p>
                                <p class="text-xs text-slate-500">{{ trans_choice(':count propuesta|:count propuestas', $row['total']) }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ $row['pending'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-emerald-600">{{ $row['approved'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-rose-600">{{ $row['rejected'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-700">
                                {{ $row['acceptance_rate'] !== null ? $row['acceptance_rate'].'%' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-700">
                                {{ $row['avg_minutes'] !== null ? $row['avg_minutes'].' min' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-slate-500">
                                {{ optional($row['last_submission_at'])->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                {{ __('No se encontraron propuestas con los filtros actuales.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </section>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Tendencia 14 días') }}</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($trend as $entry)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-900">{{ $entry['day'] }}</p>
                    <p class="text-[11px] text-slate-500">{{ __('Enviadas: :count', ['count' => $entry['submitted']]) }}</p>
                    <div class="mt-2 space-y-1 text-xs font-semibold">
                        <div class="flex items-center justify-between text-emerald-600">
                            <span>{{ __('Aprobadas') }}</span>
                            <span>{{ $entry['approved'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-rose-600">
                            <span>{{ __('Rechazadas') }}</span>
                            <span>{{ $entry['rejected'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>


