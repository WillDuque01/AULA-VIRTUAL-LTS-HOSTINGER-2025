<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Revisión docente') }}</p>
                <h1 class="text-xl font-semibold text-slate-900">{{ __('Propuestas de módulos, lecciones y packs') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Aprueba lo que suma valor, devuelve feedback cuando haga falta.') }}</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                {{ __('Estado actual:') }}
                <select wire:model="status"
                        class="border-0 bg-transparent text-slate-900 focus:ring-0">
                    <option value="pending">{{ __('Pendientes') }}</option>
                    <option value="approved">{{ __('Aprobadas') }}</option>
                    <option value="rejected">{{ __('Rechazadas') }}</option>
                </select>
            </div>
        </div>
    </header>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
        <div class="grid gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Docente') }}
                <select wire:model="filters.teacher_id"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($teacherOptions as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Curso') }}
                <select wire:model="filters.course_id"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900">
                    <option value="">{{ __('Todos') }}</option>
                    @foreach($courseOptions as $course)
                        <option value="{{ $course->id }}">{{ $course->slug }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Tipo de propuesta') }}
                <select wire:model="filters.type"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900">
                    <option value="all">{{ __('Todas') }}</option>
                    <option value="module">{{ __('Módulos') }}</option>
                    <option value="lesson">{{ __('Lecciones') }}</option>
                    <option value="pack">{{ __('Packs') }}</option>
                </select>
            </label>
        </div>
        <div class="grid gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Contenido publicado') }}
                <select wire:model="filters.content_status"
                        class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900">
                    <option value="all">{{ __('Todos') }}</option>
                    <option value="pending">{{ __('Pendiente') }}</option>
                    <option value="published">{{ __('Publicado') }}</option>
                    <option value="rejected">{{ __('Rechazado') }}</option>
                </select>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Desde') }}
                <input type="date"
                       wire:model="filters.date_from"
                       class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900" />
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Hasta') }}
                <input type="date"
                       wire:model="filters.date_to"
                       class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-slate-900 focus:ring-slate-900" />
            </label>
        </div>
        @forelse($submissions as $submission)
            @php
                $contentStatus = optional($submission->result)->status;
            @endphp
            <article class="rounded-2xl border border-slate-100 px-4 py-3">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $submission->title }}</p>
                        <p class="text-xs text-slate-500">
                            {{ ucfirst($submission->type) }} · {{ $submission->author?->name ?? $submission->author?->email }}
                            @if($submission->course)
                                · {{ data_get($submission->course->i18n->first(), 'title', $submission->course->slug) }}
                            @endif
                        </p>
                        @if($contentStatus)
                            <p class="text-[11px] text-slate-500 mt-1">
                                {{ __('Contenido: :status', ['status' => __($contentStatus)]) }}
                            </p>
                        @endif
                        @if($submission->summary)
                            <p class="mt-2 text-sm text-slate-600">{{ $submission->summary }}</p>
                        @endif
                        @if($submission->feedback && $submission->status !== 'pending')
                            <p class="mt-2 text-xs text-amber-600">{{ __('Feedback: :feedback', ['feedback' => $submission->feedback]) }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-stretch gap-2 md:w-1/3">
                        @if($submission->status === 'pending')
                            <textarea wire:model.defer="feedback.{{ $submission->id }}"
                                      class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                                      placeholder="{{ __('Comentarios opcionales') }}"></textarea>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="approve({{ $submission->id }})"
                                        class="flex-1 rounded-full bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                    {{ __('Aprobar y publicar') }}
                                </button>
                                <button type="button" wire:click="reject({{ $submission->id }})"
                                        class="flex-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:border-rose-300">
                                    {{ __('Rechazar') }}
                                </button>
                            </div>
                        @else
                            <span class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ $submission->status === 'approved' ? __('Aprobado') : __('Rechazado') }}
                            </span>
                        @endif
                    </div>
                </div>
                @if($submission->history->isNotEmpty())
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Historial') }}</p>
                        <ol class="mt-2 space-y-2">
                            @foreach($submission->history as $history)
                                <li class="rounded-xl border border-slate-100 bg-white px-3 py-2 text-[11px] text-slate-600">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-slate-800">{{ __($history->status) }}</span>
                                        <span class="text-[10px] text-slate-400">{{ optional($history->created_at)->format('d M H:i') }}</span>
                                    </div>
                                    @if($history->notes)
                                        <p class="mt-1 text-slate-500">{{ $history->notes }}</p>
                                    @endif
                                    @if($history->reviewer)
                                        <p class="mt-1 text-[10px] text-slate-400">{{ __('Por :name', ['name' => $history->reviewer->name]) }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </article>
        @empty
            <p class="text-sm text-slate-500">{{ __('No hay propuestas en esta bandeja.') }}</p>
        @endforelse

        <div>
            {{ $submissions->links() }}
        </div>
    </section>
</div>

