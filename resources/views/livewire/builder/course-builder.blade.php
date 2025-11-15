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

    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-blue-500 tracking-wide">Capítulos</p>
            <p class="mt-1 text-2xl font-bold text-blue-700">{{ count($state['chapters']) }}</p>
            <p class="text-xs text-blue-500/80">Drag & drop disponible</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-emerald-500 tracking-wide">Total lecciones</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">
                {{ collect($state['chapters'])->sum(fn($chapter) => count($chapter['lessons'])) }}
            </p>
            <p class="text-xs text-emerald-500/80">Incluye videos, quizzes y más</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase font-semibold text-amber-500 tracking-wide">Bloqueos activos</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">
                {{ collect($state['chapters'])->flatMap(fn($chapter) => $chapter['lessons'])->where('locked', true)->count() }}
            </p>
            <p class="text-xs text-amber-500/80">Controla el progreso del alumno</p>
        </div>
    </div>

    <div class="space-y-4" data-sortable-chapters>
        @forelse($state['chapters'] as $chapterIndex => $chapter)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-lg shadow-slate-100/60 p-4 space-y-4 transition hover:border-blue-100" data-chapter-item data-chapter-id="{{ $chapter['id'] }}" wire:key="chapter-{{ $chapter['id'] }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-9 h-9 cursor-move bg-white shadow-inner" title="Arrastrar capítulo">
                            <span class="text-lg leading-none select-none">⋮⋮</span>
                        </span>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Título del capítulo</label>
                            <input type="text"
                                   wire:model.defer="state.chapters.{{ $chapterIndex }}.title"
                                   wire:blur="saveChapterTitle({{ $chapter['id'] }}, {{ $chapterIndex }})"
                                   class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition"
                                   placeholder="Capítulo sin título">
                            @error("state.chapters.$chapterIndex.title")
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
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
                        <div class="border border-gray-200 rounded-2xl p-4 bg-gradient-to-br from-slate-50 to-white shadow-sm ring-1 ring-transparent data-[state=saving]:ring-blue-200" data-lesson-item data-lesson-id="{{ $lesson['id'] }}" wire:key="lesson-{{ $lesson['id'] }}">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="drag-handle inline-flex items-center justify-center rounded-full border border-dashed border-gray-400 text-gray-400 w-8 h-8 cursor-move bg-white shadow-inner" title="Arrastrar lección">
                                        <span class="text-base leading-none">⋮⋮</span>
                                    </span>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Título</label>
                                        <input type="text"
                                               wire:model.defer="state.chapters.{{ $chapterIndex }}.lessons.{{ $lessonIndex }}.title"
                                               class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               placeholder="Título de la lección">
                                        @error("state.chapters.$chapterIndex.lessons.$lessonIndex.title")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
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
                                    <span>Configuración avanzada</span>
                                    <span class="text-xs text-slate-500" x-text="open ? 'Cerrar' : 'Expandir'"></span>
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
        </style>
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

                window.addEventListener('builder:flash', (event) => {
                    showToast(event.detail || {});
                    if ((event.detail?.variant ?? 'success') === 'success') {
                        launchConfetti();
                    }
                });
            });
        </script>
    @endpush
@endonce


