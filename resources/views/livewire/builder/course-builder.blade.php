@php
    $currentLocale = app()->getLocale();
    $plannerRoute = \Illuminate\Support\Facades\Route::has('professor.discord-practices')
        ? route('professor.discord-practices', ['locale' => $currentLocale])
        : null;
    $packsManagerRoute = \Illuminate\Support\Facades\Route::has('professor.practice-packs')
        ? route('professor.practice-packs', ['locale' => $currentLocale])
        : null;
@endphp

<div
    class="space-y-6"
    data-builder-root
    data-state="idle"
    x-data="builderHotkeys({
        addChapter: () => $wire.addChapter(),
        saveFocusedLesson: () => $wire.call('saveFocusedLesson'),
        cycleTab: (dir) => $wire.call('cycleFocusTab', dir),
    })"
    x-init="init()"
>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold">{{ __('builder.heading', ['slug' => $course->slug]) }}</h2>
            <p class="text-sm text-gray-500">{{ __('builder.description') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button"
                    wire:click="addChapter"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    aria-label="{{ __('builder.actions.add_chapter_aria') }}"
                    title="{{ __('builder.actions.add_chapter_title') }}">
                <span class="text-lg">+</span>
                {{ __('builder.actions.add_chapter') }}
            </button>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $totals = data_get($metrics, 'totals', []);
        @endphp
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-blue-500 tracking-wide">{{ __('builder.metrics.chapters') }}</p>
            <p class="mt-1 text-2xl font-bold text-blue-700"
               x-data="animatedCount({{ (int) data_get($totals, 'chapters', 0) }})"
               x-text="display">
                {{ data_get($totals, 'chapters', 0) }}
            </p>
            <p class="text-xs text-blue-500/80">{{ __('builder.metrics.drag_hint') }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-emerald-500 tracking-wide">{{ __('builder.metrics.total_lessons') }}</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700"
               x-data="animatedCount({{ (int) data_get($totals, 'lessons', 0) }})"
               x-text="display">
                {{ data_get($totals, 'lessons', 0) }}
            </p>
            <p class="text-xs text-emerald-500/80">{{ __('builder.metrics.lessons_hint') }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-amber-500 tracking-wide">{{ __('builder.metrics.locks') }}</p>
            <p class="mt-1 text-2xl font-bold text-amber-700"
               x-data="animatedCount({{ (int) data_get($totals, 'locked', 0) }})"
               x-text="display">
                {{ data_get($totals, 'locked', 0) }}
            </p>
            <p class="text-xs text-amber-500/80">
                {{ __('builder.metrics.locks_hint', ['hours' => number_format(data_get($totals, 'estimated_minutes', 0) / 60, 1)]) }}
            </p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white/80 shadow-sm px-4 py-3" x-data="{ open: false }" x-on:builder-shortcuts:toggle.window="open = !open">
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('builder.shortcuts.title') }}</p>
            <button type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-300"
                    @click="open = ! open"
                    :aria-expanded="open.toString()">
                <span class="text-base" aria-hidden="true">⌨️</span>
                <span x-text="open ? '{{ __('builder.shortcuts.toggle_hide') }}' : '{{ __('builder.shortcuts.toggle_show') }}'"></span>
            </button>
            <span class="ml-auto hidden text-[11px] text-slate-400 md:inline">{{ __('builder.shortcuts.tagline') }}</span>
        </div>
        <div class="mt-3 grid gap-3 text-xs text-slate-600" x-show="open" x-transition>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-slate-800">{{ __('builder.shortcuts.tip_new_chapter_title') }}</p>
                    <p class="text-[11px] text-slate-500">{{ __('builder.shortcuts.tip_new_chapter_hint') }}</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 font-semibold text-slate-700">
                    N
                </span>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-slate-800">{{ __('builder.shortcuts.tip_save_title') }}</p>
                    <p class="text-[11px] text-slate-500">{{ __('builder.shortcuts.tip_save_hint') }}</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 font-semibold text-slate-700">
                    Ctrl/⌘ + S
                </span>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
                <p class="font-semibold text-slate-800">{{ __('builder.shortcuts.tip_accessible_title') }}</p>
                <p class="text-[11px] text-slate-500">{{ __('builder.shortcuts.tip_accessible_hint') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-indigo-100 bg-white/80 shadow-sm px-4 py-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-500">{{ __('builder.filter.title') }}</p>
                <p class="text-sm text-slate-500">{{ __('builder.filter.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach($statusFilterOptions as $value => $label)
                    <button type="button"
                            wire:click="$set('statusFilter', '{{ $value }}')"
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition
                                {{ $statusFilter === $value ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}">
                        {{ __($label) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-4" data-sortable-chapters x-data="courseBuilderDnD()" x-init="init()">
        @forelse($state['chapters'] as $chapterIndex => $chapter)
            @php
                $chapterMetrics = $metrics['chapters'][$chapter['id']] ?? null;
                $chapterStatus = $chapter['status'] ?? 'published';
                $chapterStatusClasses = match($chapterStatus) {
                    'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                    'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                    default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                };
                $showChapter = $statusFilter === 'all' ||
                    $chapterStatus === $statusFilter ||
                    collect($chapter['lessons'])->contains(fn ($lesson) => ($lesson['status'] ?? 'published') === $statusFilter);
            @endphp
            @continue(! $showChapter)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-lg shadow-slate-100/60 p-4 space-y-4 transition hover:border-blue-100" data-chapter-item data-chapter-id="{{ $chapter['id'] }}" wire:key="chapter-{{ $chapter['id'] }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-9 h-9 cursor-move bg-white shadow-inner"
                              role="button"
                              tabindex="0"
                              aria-label="{{ __('builder.drag.chapter_label') }}"
                              data-tooltip="{{ __('builder.drag.chapter_hint') }}">
                            <span class="text-lg leading-none select-none">⋮⋮</span>
                        </span>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('builder.chapter.title_label') }}</label>
                            <input type="text"
                                   wire:model.defer="state.chapters.{{ $chapterIndex }}.title"
                                   wire:blur="saveChapterTitle({{ $chapter['id'] }}, {{ $chapterIndex }})"
                                   class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition"
                                   placeholder="{{ __('builder.chapter.title_placeholder') }}">
                            @error("state.chapters.$chapterIndex.title")
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                            <span class="mt-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $chapterStatusClasses }}">
                                {{ __('Estado: :status', ['status' => __($chapterStatus)]) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'video')" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md text-xs font-semibold">{{ __('builder.lessons.add.video') }}</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'text')" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-md text-xs font-semibold">{{ __('builder.lessons.add.text') }}</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'pdf')" class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-md text-xs font-semibold">{{ __('builder.lessons.add.pdf') }}</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'quiz')" class="px-3 py-1.5 bg-rose-100 text-rose-700 rounded-md text-xs font-semibold">{{ __('builder.lessons.add.quiz') }}</button>
                        <button type="button" wire:click="removeChapter({{ $chapter['id'] }})" class="inline-flex items-center px-3 py-1.5 border border-red-200 text-red-600 rounded-md text-xs font-semibold hover:bg-red-50">
                            <span class="text-sm">✕</span>
                            {{ __('builder.actions.remove') }}
                        </button>
                    </div>
                </div>

                @if($chapterMetrics)
                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                            📚 {{ $chapterMetrics['lessons'] }} lecciones
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-2 py-0.5 text-amber-700">
                            🔒 {{ $chapterMetrics['locked'] }} bloqueadas
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 px-2 py-0.5 text-emerald-700">
                            ⏱ {{ $chapterMetrics['estimated_minutes'] }} min estimados
                        </span>
                        @if($chapterMetrics['assignments'] ?? 0)
                            <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 px-2 py-0.5 text-indigo-700">
                                🧾 {{ $chapterMetrics['assignments'] }} tareas
                            </span>
                        @endif
                    </div>
                @endif

                <div class="space-y-3" data-sortable-lessons>
                    @forelse($chapter['lessons'] as $lessonIndex => $lesson)
                        @php
                            $isFocused = $focus && data_get($focus, 'lesson.id') === $lesson['id'];
                            $isSaving = ($savingLessonId ?? null) === ($lesson['id'] ?? null);
                            $lessonStatus = $lesson['status'] ?? 'published';
                            $lessonStatusClasses = match($lessonStatus) {
                                'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                                'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                                default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            };
                        @endphp
                        @continue($statusFilter !== 'all' && $lessonStatus !== $statusFilter)
                        <div @class([
                                'relative border rounded-2xl p-4 bg-gradient-to-br from-slate-50 to-white shadow-sm ring-1 ring-transparent transition data-[state=saving]:opacity-80',
                                'border-indigo-200 ring-2 ring-indigo-200/80 bg-white shadow-lg shadow-indigo-100/60' => $isFocused,
                                'border-gray-200' => ! $isFocused,
                            ])
                             data-lesson-item
                             data-lesson-id="{{ $lesson['id'] }}"
                             data-state="{{ $isSaving ? 'saving' : 'idle' }}"
                             aria-busy="{{ $isSaving ? 'true' : 'false' }}"
                             wire:key="lesson-{{ $lesson['id'] }}">
                            @if($isSaving)
                                <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 rounded-2xl">
                                    <svg class="h-5 w-5 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-8 h-8 cursor-move bg-white shadow-inner"
                                          role="button"
                                          tabindex="0"
                                          aria-label="{{ __('builder.drag.lesson_label') }}"
                                          data-tooltip="{{ __('builder.drag.lesson_hint') }}">
                                        <span class="text-base leading-none">⋮⋮</span>
                                    </span>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">{{ __('builder.lessons.title_label') }}</label>
                                        <input type="text"
                                               wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.title"
                                               class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="{{ __('builder.lessons.title_placeholder') }}">
                                        @error("state.chapters.$chapterIndex.lessons.$lessonIndex.title")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                        <span class="mt-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $lessonStatusClasses }}">
                                            {{ __('Estado: :status', ['status' => __($lessonStatus)]) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">{{ __('builder.lessons.type_label') }}</label>
                                        <select wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.type" class="mt-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @foreach($lessonTypes as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="inline-flex items-center gap-2 mt-5">
                                        <input type="checkbox" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.locked" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">{{ __('builder.lessons.lock_toggle') }}</span>
                                    </label>
                                    <button type="button"
                                            wire:click="focusLesson({{ $lesson['id'] }})"
                                            class="mt-5 inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $isFocused ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-300' }}"
                                            aria-pressed="{{ $isFocused ? 'true' : 'false' }}"
                                            title="{{ $isFocused ? __('builder.focus.actions.lesson_active') : __('builder.focus.actions.lesson_focus') }}">
                                        <span aria-hidden="true">{{ $isFocused ? '✨' : '👁' }}</span>
                                        {{ $isFocused ? __('builder.focus.actions.chip_active') : __('builder.focus.actions.chip_focus') }}
                                    </button>
                                    <button type="button"
                                            wire:click="removeLesson({{ $lesson['id'] }})"
                                            class="mt-5 inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-700"
                                            title="{{ __('builder.lessons.remove') }}">
                                        <span class="text-sm">✕</span>
                                        {{ __('builder.lessons.remove') }}
                                    </button>
                                </div>
                            </div>

                            @php
                                $practiceMeta = $lesson['practice_meta'] ?? null;
                                $packMeta = $lesson['pack_meta'] ?? null;
                                $nextPracticeLabel = $practiceMeta && ! empty($practiceMeta['next_start'])
                                    ? \Illuminate\Support\Carbon::parse($practiceMeta['next_start'])->translatedFormat('d M H:i')
                                    : null;
                            @endphp

                            <div class="mt-3 rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 text-[11px] font-semibold text-slate-600">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($practiceMeta)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-white px-3 py-1 text-indigo-700">
                                            🎙️ {{ __('builder.focus.practice.practice_label') }} · {{ $practiceMeta['total'] }}
                                            @if($nextPracticeLabel)
                                                · {{ $nextPracticeLabel }}
                                            @endif
                                            @if($practiceMeta['requires_pack'] ?? false)
                                                · {{ __('builder.focus.practice.pack_required') }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-dashed border-slate-200 px-3 py-1 text-slate-400">
                                            {{ __('builder.focus.practice.none') }}
                                        </span>
                                    @endif

                                    @if($packMeta)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-white px-3 py-1 text-emerald-700">
                                            💼 {{ $packMeta['title'] ?? __('builder.focus.practice.pack_assigned') }}
                                            @if(!empty($packMeta['sessions']))
                                                · {{ $packMeta['sessions'] }} {{ __('builder.focus.practice.sessions') }}
                                            @endif
                                            @if(!empty($packMeta['price']))
                                                · ${{ number_format($packMeta['price'], 0) }} {{ $packMeta['currency'] }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-dashed border-slate-200 px-3 py-1 text-slate-400">
                                            {{ __('builder.focus.practice.no_pack') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px] font-semibold text-slate-500">
                                    @if($plannerRoute)
                                        <a href="{{ $plannerRoute }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-white px-3 py-1 text-indigo-700 hover:border-indigo-300 hover:text-indigo-800">
                                            {{ __('builder.focus.practice.open_planner') }} ↗
                                        </a>
                                    @endif
                                    @if($packsManagerRoute)
                                        <a href="{{ $packsManagerRoute }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-white px-3 py-1 text-emerald-700 hover:border-emerald-300 hover:text-emerald-800">
                                            {{ __('builder.focus.practice.manage_packs') }} ↗
                                        </a>
                                    @endif
                                </div>
                            </div>

                        @if(($lesson['type'] ?? '') === 'assignment')
                            @php
                                $assignmentStats = $lesson['stats'] ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];
                            @endphp
                            <div class="mt-3 space-y-2">
                                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">
                                        <span class="text-xs">🕒</span>
                                        {{ __('builder.assignments.stats.pending') }}: {{ $assignmentStats['pending'] ?? 0 }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                                        <span class="text-xs">✅</span>
                                        {{ __('builder.assignments.stats.approved') }}: {{ $assignmentStats['approved'] ?? 0 }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-700">
                                        <span class="text-xs">⚠️</span>
                                        {{ __('builder.assignments.stats.rejected') }}: {{ $assignmentStats['rejected'] ?? 0 }}
                                    </span>
                                    @if($lesson['requires_approval'] ?? false)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700">
                                            <span class="text-xs">🛡️</span>
                                            {{ __('builder.assignments.requires_approval') }}
                                            @if($lesson['passing_score'])
                                                · {{ __('builder.assignments.passing_score', ['score' => $lesson['passing_score']]) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">
                                    {{ __('builder.assignments.stats.hint') }}
                                </p>
                                @php
                                    $builderWhatsLink = \App\Support\Integrations\WhatsAppLink::assignment(
                                        [
                                            'title' => $lesson['title'] ?? 'Tarea',
                                            'status' => 'pending',
                                        ],
                                        'builder.course-builder',
                                        ['lesson_id' => $lesson['id']]
                                    );
                                @endphp
                                @if($builderWhatsLink)
                                    <a href="{{ $builderWhatsLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-400">
                                        {{ __('whatsapp.assignment.followup_cta') }} ↗
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 grid gap-3 md:grid-cols-3">
                                @if(($lesson['type'] ?? '') === 'video')
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Proveedor</label>
                                        <select wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.source" class="mt-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @foreach($videoSources as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">ID del video / asset</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.video_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. YTdQw4w9WgXcQ">
                                        @error("state.chapters.$chapterIndex.lessons.$lessonIndex.video_id")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Duración (seg)</label>
                                        <input type="number" min="0" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.length" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="600">
                                    </div>
                                @elseif(($lesson['type'] ?? '') === 'text')
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Contenido (Markdown/HTML breve)</label>
                                        <textarea wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.body" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Escribe el contenido, puedes usar Markdown básico..."></textarea>
                                    </div>
                                @elseif(($lesson['type'] ?? '') === 'quiz')
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Referencia de banco</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.quiz_ref" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. a1-colores">
                                        <p class="text-xs text-gray-500 mt-1">Opcional. Usa un slug para enlazar con seeds o plantillas de preguntas.</p>
                                    </div>
                                @elseif(($lesson['type'] ?? '') === 'assignment')
                                    <div class="md:col-span-3 space-y-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Instrucciones</label>
                                            <textarea wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.instructions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Describe la tarea y recursos recomendados..."></textarea>
                                        </div>
                                        <div class="grid gap-3 md:grid-cols-3">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Fecha límite</label>
                                                <input type="datetime-local" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.due_at" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Puntaje máximo</label>
                                                <input type="number" min="1" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.max_points" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="100">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">% mínimo para aprobar</label>
                                                <input type="number" min="0" max="100" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.passing_score" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="70">
                                                <p class="text-[11px] text-slate-500 mt-1">Se aplica si requiere aprobación docente.</p>
                                                @error("state.chapters.$chapterIndex.lessons.$lessonIndex.passing_score")
                                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-2">
                                            <div>
                            <p class="text-[11px] uppercase font-semibold text-slate-500">Aprobación docente</p>
                                                <p class="text-xs text-slate-500">Bloquea el avance hasta que un profesor califique la entrega.</p>
                                            </div>
                                            <label class="inline-flex items-center gap-2">
                                                <input type="checkbox" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.requires_approval" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-xs font-semibold text-slate-700">Requerir aprobación</span>
                                            </label>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Rúbrica (1 criterio por línea)</label>
                                            <textarea wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.rubric" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Comprensión&#10;Gramática&#10;Pronunciación"></textarea>
                                        </div>
                                    </div>
                                @else
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Recurso / URL</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.resource_url" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://"> 
                                        @error("state.chapters.$chapterIndex.lessons.$lessonIndex.resource_url")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <div x-data="{ open: false }" class="mt-3 rounded-xl border border-dashed border-slate-200 bg-white/80 px-3 py-2">
                                <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-sm font-semibold text-slate-700">
                                    <span>{{ __('builder.advanced.title') }}</span>
                                    <span class="text-xs text-slate-500" x-text="open ? '{{ __('builder.advanced.close') }}' : '{{ __('builder.advanced.open') }}'"></span>
                                </button>
                                <div x-show="open" x-transition.duration.200ms class="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Minutos estimados</label>
                                        <input type="number" min="0" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.estimated_minutes" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="10">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Badge / Emoji</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.badge" maxlength="24" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="🔥 Clave">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Liberar el</label>
                                        <input type="datetime-local" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.release_at" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CTA label</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.cta_label" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Agenda sesión">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CTA URL</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.cta_url" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://">
                                        @error("state.chapters.$chapterIndex.lessons.$lessonIndex.cta_url")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prerequisito</label>
                                        <select wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.prerequisite_lesson_id" class="mt-1 block w-full rounded-md border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Sin prerequisito</option>
                                            @foreach($availablePrerequisites as $lessonId => $label)
                                                @if($lessonId !== $lesson['id'])
                                                    <option value="{{ $lessonId }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-right">
                                <button type="button"
                                        wire:click="saveLesson({{ $chapterIndex }}, {{ $lessonIndex }})"
                                        wire:loading.attr="disabled"
                                        wire:target="saveLesson({{ $chapterIndex }}, {{ $lessonIndex }})"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70">
                                    <svg wire:loading.flex wire:target="saveLesson({{ $chapterIndex }}, {{ $lessonIndex }})" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Guardar cambios
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 italic">{{ __('builder.lessons.empty') }}</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                {{ __('builder.chapter.empty_state') }}
            </div>
        @endforelse
    </div>
    @if($focus)
        @php
            $focusLesson = data_get($focus, 'lesson', []);
            $focusChapter = data_get($focus, 'chapter', []);
            $focusLessonId = $focusLesson['id'] ?? null;
            $practiceMeta = $focusLesson['practice_meta'] ?? null;
            $packMeta = $focusLesson['pack_meta'] ?? null;
        @endphp
        @php
            $focusTabs = [
                'content' => __('builder.focus.tabs.content'),
                'config' => __('builder.focus.tabs.config'),
                'practice' => __('builder.focus.tabs.practice'),
                'gamification' => __('builder.focus.tabs.gamification'),
            ];
        @endphp
        <div class="fixed inset-x-4 bottom-4 sm:bottom-6 sm:right-6 sm:left-auto w-auto max-w-full sm:max-w-3xl rounded-3xl border border-slate-200 bg-white/95 shadow-2xl shadow-slate-500/20 backdrop-blur"
             wire:key="builder-focus-panel">
            <div class="p-5 space-y-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-[11px] uppercase font-semibold tracking-wide text-slate-400">{{ __('builder.focus.panel_label') }}</p>
                        <h3 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                            {{ $focusLesson['title'] ?? __('builder.focus.default_lesson') }}
                            @if($focusLesson['badge'] ?? null)
                                <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600">
                                    {{ $focusLesson['badge'] }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ $focusChapter['title'] ?? __('builder.focus.chapter_fallback') }} · {{ ucfirst($focusLesson['type'] ?? 'bloque') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-slate-500">
                        @foreach($focusTabs as $tabKey => $tabLabel)
                            <button type="button"
                                    wire:click="setFocusTab('{{ $tabKey }}')"
                                    @class([
                                        'rounded-full px-3 py-1 transition border',
                                        'bg-slate-900 text-white border-slate-900 shadow' => $focusTab === $tabKey,
                                        'bg-white border-slate-200 hover:border-slate-300 text-slate-600' => $focusTab !== $tabKey,
                                    ])>
                                {{ $tabLabel }}
                            </button>
                        @endforeach
                        <button type="button"
                                wire:click="clearFocus"
                                class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            ✕ {{ __('builder.focus.actions.close') }}
                        </button>
                    </div>
                </div>

                @if($focusLessonId)
                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold text-slate-600">
                        <button type="button"
                                wire:click="duplicateLesson({{ $focusLessonId }})"
                                class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-1.5 text-white shadow hover:bg-slate-800">
                            ✨ Duplicar lección
                        </button>

                        <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1">
                            <span>{{ __('builder.focus.actions.move_to') }}</span>
                            <select class="bg-transparent text-xs focus:outline-none"
                                    wire:change="quickMoveLesson({{ $focusLessonId }}, $event.target.value)">
                                <option value="">{{ __('builder.focus.actions.select_chapter_option') }}</option>
                                @foreach($state['chapters'] as $chapterOption)
                                    <option value="{{ $chapterOption['id'] }}" @selected(($chapterOption['id'] ?? null) === ($focusChapter['id'] ?? null))>
                                        {{ $chapterOption['title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1">
                            <span>{{ __('builder.focus.actions.convert_to') }}</span>
                            <select class="bg-transparent text-xs focus:outline-none"
                                    wire:change="quickConvertLesson({{ $focusLessonId }}, $event.target.value)">
                                <option value="">{{ __('builder.focus.actions.select_type_option') }}</option>
                                @foreach($lessonTypes as $value => $label)
                                    @if($value !== ($focusLesson['type'] ?? null))
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </label>
                    </div>
                @endif

                @php
                    $baseChips = [
                        ['show' => $focusLesson['locked'] ?? false, 'label' => __('builder.focus.chips.blocks_progress'), 'icon' => '🔒', 'classes' => 'border-amber-200 bg-amber-50 text-amber-700'],
                        ['show' => !empty($focusLesson['estimated_minutes']), 'label' => ($focusLesson['estimated_minutes'] ?? 0).' '. __('builder.focus.chips.minutes'), 'icon' => '⏱', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                        ['show' => !empty($focusLesson['release_at']), 'label' => __('builder.focus.chips.release_on').' '.\Illuminate\Support\Carbon::parse($focusLesson['release_at'])->translatedFormat('d M H:i'), 'icon' => '📅', 'classes' => 'border-slate-200 bg-slate-50 text-slate-600'],
                        ['show' => $focusChapter && ($focusChapter['metrics']['lessons'] ?? false), 'label' => ($focusChapter['metrics']['lessons'] ?? 0).' '. __('builder.focus.chips.lessons_in_chapter'), 'icon' => '📚', 'classes' => 'border-indigo-100 bg-indigo-50 text-indigo-700'],
                    ];
                @endphp
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                    @foreach(($baseChips ?? []) as $chip)
                        @if($chip['show'])
                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 {{ $chip['classes'] }}">
                                <span aria-hidden="true">{{ $chip['icon'] }}</span> {{ $chip['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                    @switch($focusTab)
                        @case('content')
                            <div class="grid gap-3 md:grid-cols-2 text-sm text-slate-600">
                                <div>
                                    <p class="text-[11px] uppercase font-semibold text-slate-400">{{ __('builder.focus.content.details') }}</p>
                                    <ul class="mt-2 space-y-1">
                                        <li>• {{ __('builder.focus.content.type') }}: {{ ucfirst($focusLesson['type'] ?? 'bloque') }}</li>
                                        <li>• {{ __('builder.focus.content.duration') }}: {{ $focusLesson['length'] ?? '—' }} {{ __('builder.focus.content.seconds') }}</li>
                                        <li>• {{ __('builder.focus.content.prerequisite') }}: {{ $focusLesson['prerequisite_lesson_id'] ? __('builder.focus.content.yes') : __('builder.focus.content.no') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase font-semibold text-slate-400">{{ __('builder.focus.content.cta') }}</p>
                                    @if(!empty($focusLesson['cta_label']) && !empty($focusLesson['cta_url']))
                                        <p class="mt-1 font-semibold text-slate-900">{{ $focusLesson['cta_label'] }}</p>
                                        <p class="text-xs text-slate-500 break-all">{{ $focusLesson['cta_url'] }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-slate-500">{{ __('builder.focus.content.cta_none') }}</p>
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('config')
                            <div class="grid gap-3 md:grid-cols-2 text-sm text-slate-600">
                                <div>
                                    <p class="text-[11px] uppercase font-semibold text-slate-400">{{ __('builder.focus.config_cards.locks') }}</p>
                                    <ul class="mt-2 space-y-1">
                                        <li>• {{ __('builder.focus.config_cards.locked') }}: {{ ($focusLesson['locked'] ?? false) ? __('builder.focus.content.yes') : __('builder.focus.content.no') }}</li>
                                        <li>• {{ __('builder.focus.config_cards.scheduled') }}: {{ $focusLesson['release_at'] ? \Illuminate\Support\Carbon::parse($focusLesson['release_at'])->diffForHumans() : __('builder.focus.config_cards.na') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase font-semibold text-slate-400">{{ __('builder.focus.config_cards.metadata') }}</p>
                                    <ul class="mt-2 space-y-1">
                                        <li>• {{ __('builder.focus.config_cards.badge') }}: {{ $focusLesson['badge'] ?? __('builder.focus.config_cards.na') }}</li>
                                        <li>• {{ __('builder.focus.config_cards.cta_label') }}: {{ $focusLesson['cta_label'] ?? __('builder.focus.config_cards.na') }}</li>
                                        <li>• {{ __('builder.focus.config_cards.cta_url') }}: {{ $focusLesson['cta_url'] ? __('builder.focus.config_cards.defined') : __('builder.focus.config_cards.pending') }}</li>
                                    </ul>
                                </div>
                            </div>
                            @break

                        @case('practice')
                            <div class="text-sm text-slate-600 space-y-3">
                                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                                    @if($practiceMeta)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-indigo-700">
                                            🎙️ {{ __('builder.focus.practice.active') }}: {{ $practiceMeta['total'] }}
                                        </span>
                                        @if($practiceMeta['next_start'] ?? null)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600">
                                                ⏭ {{ __('builder.focus.practice.next') }}: {{ \Illuminate\Support\Carbon::parse($practiceMeta['next_start'])->translatedFormat('d M H:i') }}
                                            </span>
                                        @endif
                                        @if($practiceMeta['requires_pack'] ?? false)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">
                                                ⚠️ {{ __('builder.focus.practice.requires_pack') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-500">{{ __('builder.focus.practice.empty_state') }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                                    @if($packMeta)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                                            💼 {{ $packMeta['title'] ?? __('builder.focus.practice.pack_assigned') }}
                                        </span>
                                        <span class="text-xs text-slate-500">{{ $packMeta['sessions'] ?? '—' }} {{ __('builder.focus.practice.sessions') }} · ${{ number_format($packMeta['price'] ?? 0, 0) }} {{ $packMeta['currency'] ?? '' }}</span>
                                    @else
                                        <span class="text-xs text-slate-500">{{ __('builder.focus.practice.no_pack') }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if($plannerRoute ?? false)
                                        <a href="{{ $plannerRoute }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1.5 text-xs font-semibold text-indigo-700 hover:border-indigo-300">
                                            {{ __('builder.focus.practice.open_planner') }} ↗
                                        </a>
                                    @endif
                                    @if($packsManagerRoute ?? false)
                                        <a href="{{ $packsManagerRoute }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                                            {{ __('builder.focus.practice.manage_packs') }} ↗
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('gamification')
                            <div class="space-y-3">
                                @if(!empty($focusLesson['stats']))
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                        <p class="text-[11px] uppercase font-semibold text-slate-500 tracking-wide mb-2">{{ __('Estado de tareas vinculadas') }}</p>
                                        <div class="flex flex-wrap gap-2 text-[12px] font-semibold">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-white px-3 py-1 text-amber-700">
                                                🕒 {{ __('Pendientes') }}: {{ $focusLesson['stats']['pending'] ?? 0 }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-white px-3 py-1 text-emerald-700">
                                                ✅ {{ __('Aprobadas') }}: {{ $focusLesson['stats']['approved'] ?? 0 }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-white px-3 py-1 text-rose-700">
                                                ⚠️ {{ __('Rechazadas') }}: {{ $focusLesson['stats']['rejected'] ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500">{{ __('Esta lección no tiene tarefas o quizzes vinculados aún.') }}</p>
                                @endif
                            </div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('builderHotkeys', (actions = {}) => ({
            init() {
                this.boundHandler = this.handleKeydown.bind(this);
                window.addEventListener('keydown', this.boundHandler);
            },
            destroy() {
                window.removeEventListener('keydown', this.boundHandler);
            },
            handleKeydown(event) {
                const activeElement = document.activeElement;
                if (activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName)) {
                    return;
                }
                if (event.target?.isContentEditable) {
                    return;
                }

                // Guardar lección enfocada
                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    actions.saveFocusedLesson?.();
                    window.dispatchEvent(new CustomEvent('builder:flash', {
                        detail: {
                            variant: 'success',
                            message: '{{ __('builder.notifications.lesson_saved') }}'
                        }
                    }));

                    return;
                }

                if (event.shiftKey && event.key === '?') {
                    event.preventDefault();
                    window.dispatchEvent(new CustomEvent('builder-shortcuts:toggle'));

                    return;
                }

                if (event.key === '[' || event.key === ']') {
                    event.preventDefault();
                    actions.cycleTab?.(event.key === '[' ? 'prev' : 'next');

                    return;
                }

                if (! event.metaKey && ! event.ctrlKey && ! event.altKey && event.key.toLowerCase() === 'n') {
                    event.preventDefault();
                    actions.addChapter?.();
                    return;
                }
            },
        }));
    });
</script>
@endpush

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-Ros5pTKty+O+kO5OVwOB1p5MNDoAuCEi0aKBslZx2XY=" crossorigin="anonymous"></script>
        <style>
            @keyframes builder-toast {
                from { opacity: 0; transform: translateY(6px) scale(0.96); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            .builder-toast {
                animation: builder-toast 0.18s ease-out;
            }
            .builder-confetti {
                position: fixed;
                width: 6px;
                height: 12px;
                background: var(--confetti-color, #38bdf8);
                border-radius: 999px;
                pointer-events: none;
                opacity: 0;
                animation: builder-confetti-fall 0.8s ease-out forwards;
            }
            @keyframes builder-confetti-fall {
                0% { transform: translateY(-20px) scale(1); opacity: 1; }
                100% { transform: translateY(40px) scale(0.5); opacity: 0; }
            }
            [data-lesson-item][data-state="saving"] {
                pointer-events: none;
            }
            [data-builder-root][data-state="dragging"] [data-lesson-item] {
                opacity: 0.85;
                transform: scale(0.985);
            }
            [data-tooltip] {
                position: relative;
            }
            [data-tooltip]::after {
                content: attr(data-tooltip);
                position: absolute;
                left: 50%;
                bottom: calc(100% + 6px);
                transform: translateX(-50%);
                background: rgba(15, 23, 42, 0.9);
                color: white;
                font-size: 10px;
                letter-spacing: 0.02em;
                padding: 4px 8px;
                border-radius: 999px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s ease;
                z-index: 10;
            }
            [data-tooltip]:focus-visible::after,
            [data-tooltip]:hover::after {
                opacity: 1;
            }
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
                .builder-toast,
                .builder-confetti {
                    animation: none !important;
                }
            }
        </style>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('animatedCount', (targetValue = 0) => ({
                    target: Number(targetValue) || 0,
                    display: 0,
                    started: false,
                    start() {
                        if (this.started) {
                            return;
                        }

                        this.started = true;
                        const totalSteps = Math.max(12, Math.min(60, Math.round(this.target / 2) || 18));
                        let current = 0;

                        const tick = () => {
                            current += 1;
                            this.display = Math.round((this.target / totalSteps) * current);

                            if (current < totalSteps) {
                                requestAnimationFrame(tick);
                            } else {
                                this.display = this.target;
                            }
                        };

                        requestAnimationFrame(tick);
                    },
                }));
            });

            document.addEventListener('livewire:load', () => {
                let cleanupShortcuts = null;

                const callShortcut = (eventName) => {
                    if (window.Livewire?.dispatch) {
                        window.Livewire.dispatch(eventName);
                    }
                };

                const shouldIgnoreTarget = (target) => {
                    if (! target) {
                        return false;
                    }

                    const tag = target.tagName ? target.tagName.toLowerCase() : '';
                    return ['input', 'textarea', 'select'].includes(tag) || target.isContentEditable;
                };

                const registerShortcuts = () => {
                    const handler = (event) => {
                        const key = event.key ? event.key.toLowerCase() : null;
                        if (! key) {
                            return;
                        }

                        if (! event.ctrlKey && ! event.metaKey && ! event.altKey && key === 'n' && ! shouldIgnoreTarget(event.target)) {
                            event.preventDefault();
                            callShortcut('builder-new-chapter');
                        }

                        if ((event.ctrlKey || event.metaKey) && key === 's') {
                            event.preventDefault();
                            callShortcut('builder-save-focused');
                        }
                    };

                    document.addEventListener('keydown', handler);

                    return () => document.removeEventListener('keydown', handler);
                };

                const ensureShortcuts = () => {
                    if (cleanupShortcuts || ! document.querySelector('[data-builder-root]')) {
                        return;
                    }

                    cleanupShortcuts = registerShortcuts();
                };

                ensureShortcuts();

                Livewire.on('builder:refresh-sortables', () => {
                    setTimeout(() => {
                        ensureShortcuts();
                    }, 60);
                });

                const showToast = ({ variant = 'success', message = '' }) => {
                    const container = document.getElementById('builder-toasts') ?? createToastContainer();
                    const toast = document.createElement('div');
                    toast.className = `builder-toast mb-2 flex items-center gap-3 rounded-2xl px-4 py-2 text-sm font-semibold shadow-lg ${variant === 'error' ? 'bg-rose-500/90 text-white' : 'bg-emerald-500/90 text-white'}`;
                    toast.textContent = message;
                    container.appendChild(toast);
                    setTimeout(() => toast.remove(), 2800);
                };

                const createToastContainer = () => {
                    const div = document.createElement('div');
                    div.id = 'builder-toasts';
                    div.className = 'fixed bottom-6 right-6 z-50 flex flex-col items-end';
                    document.body.appendChild(div);
                    return div;
                };

                const launchConfetti = () => {
                    const colors = ['#3b82f6', '#a855f7', '#f59e0b', '#10b981'];
                    const baseX = window.innerWidth - 100;
                    const baseY = window.innerHeight - 80;
                    Array.from({ length: 12 }).forEach(() => {
                        const piece = document.createElement('span');
                        piece.className = 'builder-confetti';
                        piece.style.left = `${baseX + (Math.random() * 60 - 30)}px`;
                        piece.style.top = `${baseY}px`;
                        piece.style.setProperty('--confetti-color', colors[Math.floor(Math.random() * colors.length)]);
                        document.body.appendChild(piece);
                        setTimeout(() => piece.remove(), 900);
                    });
                };

                Livewire.on('builder:flash', (payload = {}) => {
                    showToast(payload || {});
                    if ((payload?.variant ?? 'success') === 'success') {
                        launchConfetti();
                    }
                });
                Livewire.on('builder:celebrate', () => launchConfetti());

                Livewire.on('builder:focus-open', ({ lessonId } = {}) => {
                    if (! lessonId) {
                        return;
                    }

                    const card = document.querySelector(`[data-lesson-item][data-lesson-id="${lessonId}"]`);
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                        card.animate([
                            { boxShadow: '0 0 0 rgba(79,70,229,0)' },
                            { boxShadow: '0 0 25px rgba(79,70,229,.25)' },
                            { boxShadow: '0 0 0 rgba(79,70,229,0)' }
                        ], {
                            duration: 720,
                            easing: 'ease-out'
                        });
                    }
                });

                Livewire.hook('component.removed', (component) => {
                    if (component.el && component.el.hasAttribute('data-builder-root')) {
                        cleanupShortcuts?.();
                        cleanupShortcuts = null;
                    }
                });
            });
        </script>
    @endpush
@endonce


