<div class="space-y-6" data-component-id="{{ $this->id }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold">Builder de curso: {{ $course->slug }}</h2>
            <p class="text-sm text-gray-500">Organiza capítulos, define lecciones y bloquea el avance según el plan de estudios.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="addChapter" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <span class="text-lg">+</span>
                Nuevo capítulo
            </button>
        </div>
    </div>

    <div class="space-y-4" data-sortable-chapters>
        @forelse($state['chapters'] as $chapterIndex => $chapter)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 space-y-4" data-chapter-item data-chapter-id="{{ $chapter['id'] }}" wire:key="chapter-{{ $chapter['id'] }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-8 h-8 cursor-move" title="Arrastrar capítulo">
                            <span class="text-lg leading-none">⋮⋮</span>
                        </span>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Título del capítulo</label>
                            <input type="text"
                                   wire:model.defer="state.chapters.{{ $chapterIndex }}.title"
                                   wire:blur="saveChapterTitle({{ $chapter['id'] }}, {{ $chapterIndex }})"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Capítulo sin título">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'video')" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md text-xs font-semibold">+ Video</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'text')" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-md text-xs font-semibold">+ Texto</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'pdf')" class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-md text-xs font-semibold">+ PDF</button>
                        <button type="button" wire:click="addLesson({{ $chapter['id'] }}, 'quiz')" class="px-3 py-1.5 bg-rose-100 text-rose-700 rounded-md text-xs font-semibold">+ Quiz</button>
                        <button type="button" wire:click="removeChapter({{ $chapter['id'] }})" class="inline-flex items-center px-3 py-1.5 border border-red-200 text-red-600 rounded-md text-xs font-semibold hover:bg-red-50">
                            <span class="text-sm">✕</span>
                            Eliminar
                        </button>
                    </div>
                </div>

                <div class="space-y-3" data-sortable-lessons>
                    @forelse($chapter['lessons'] as $lessonIndex => $lesson)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50" data-lesson-item data-lesson-id="{{ $lesson['id'] }}" wire:key="lesson-{{ $lesson['id'] }}">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-7 h-7 cursor-move" title="Arrastrar lección">
                                        <span class="text-base leading-none">⋮⋮</span>
                                    </span>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Título</label>
                                        <input type="text"
                                               wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.title"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="Título de la lección">
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Tipo</label>
                                        <select wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.type" class="mt-1 rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @foreach($lessonTypes as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="inline-flex items-center gap-2 mt-5">
                                        <input type="checkbox" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.locked" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">Bloquear avance</span>
                                    </label>
                                    <button type="button" wire:click="removeLesson({{ $lesson['id'] }})" class="mt-5 inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-700">
                                        <span class="text-sm">✕</span>
                                        Quitar
                                    </button>
                                </div>
                            </div>

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
                                @else
                                    <div class="md:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Recurso / URL</label>
                                        <input type="text" wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.resource_url" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://"> 
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 text-right">
                                <button type="button" wire:click="saveLesson({{ $chapterIndex }}, {{ $lessonIndex }})" class="inline-flex items-center px-4 py-2 text-xs font-semibold bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Guardar cambios
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 italic">No hay lecciones en este capítulo todavía.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center text-gray-500">
                No hay capítulos por ahora. Usa el botón “Nuevo capítulo” para comenzar.
            </div>
        @endforelse
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-Ros5pTKty+O+kO5OVwOB1p5MNDoAuCEi0aKBslZx2XY=" crossorigin="anonymous"></script>
        <script>
            document.addEventListener('livewire:load', () => {
                const initializeSortables = () => {
                    const chaptersContainer = document.querySelector('[data-sortable-chapters]');
                    if (! chaptersContainer) {
                        return;
                    }

                    if (chaptersContainer._sortable) {
                        chaptersContainer._sortable.destroy();
                    }

                    chaptersContainer._sortable = Sortable.create(chaptersContainer, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'opacity-50',
                        onEnd: dispatchOrder,
                    });

                    chaptersContainer.querySelectorAll('[data-sortable-lessons]').forEach((lessonsContainer) => {
                        if (lessonsContainer._sortable) {
                            lessonsContainer._sortable.destroy();
                        }

                        lessonsContainer._sortable = Sortable.create(lessonsContainer, {
                            group: 'lessons',
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: dispatchOrder,
                        });
                    });
                };

                const dispatchOrder = () => {
                    const chaptersContainer = document.querySelector('[data-sortable-chapters]');
                    if (! chaptersContainer) {
                        return;
                    }

                    const structure = Array.from(chaptersContainer.querySelectorAll('[data-chapter-item]')).map((chapterEl) => {
                        const lessonsContainer = chapterEl.querySelector('[data-sortable-lessons]');
                        const lessons = lessonsContainer
                            ? Array.from(lessonsContainer.querySelectorAll('[data-lesson-item]')).map((lessonEl) => ({
                                id: lessonEl.dataset.lessonId,
                            }))
                            : [];

                        return {
                            id: chapterEl.dataset.chapterId,
                            lessons,
                        };
                    });

                    window.Livewire.dispatch('builder-reorder', { chapters: structure });
                };

                initializeSortables();

                window.addEventListener('builder:refresh-sortables', () => {
                    setTimeout(() => initializeSortables(), 60);
                });
            });
        </script>
    @endpush
@endonce


