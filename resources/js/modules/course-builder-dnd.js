// [AGENTE: GPT-5.1 CODEX] - Componente Alpine para gestionar drag & drop del Course Builder
export function courseBuilderDnD() {
    return {
        chapterSortable: null,
        lessonSortables: [],
        unsubscribe: null,

        init() {
            this.subscribeToLivewire();
            this.refreshSortables();
        },

        destroy() {
            this.destroySortables();
            if (typeof this.unsubscribe === 'function') {
                this.unsubscribe();
            }
        },

        subscribeToLivewire() {
            if (window.Livewire?.on) {
                this.unsubscribe = window.Livewire.on('builder:refresh-sortables', () => this.refreshSortables());
            }
        },

        refreshSortables() {
            this.$nextTick(() => {
                if (! this.ensureSortableReady()) {
                    return;
                }

                this.destroySortables();
                this.initChapterSortable();
                this.initLessonSortables();
            });
        },

        ensureSortableReady() {
            if (window.Sortable) {
                return true;
            }

            setTimeout(() => this.refreshSortables(), 120);

            return false;
        },

        initChapterSortable() {
            this.chapterSortable = window.Sortable.create(this.$el, {
                handle: '.drag-handle',
                animation: 180,
                ghostClass: 'opacity-50',
                onStart: () => this.setBuilderState('dragging'),
                onEnd: () => {
                    this.setBuilderState('idle');
                    this.handleStructureChange();
                },
            });
        },

        initLessonSortables() {
            this.lessonSortables = [];
            this.$el.querySelectorAll('[data-sortable-lessons]').forEach((container) => {
                const sortable = window.Sortable.create(container, {
                    group: 'builder-lessons',
                    handle: '.drag-handle',
                    animation: 180,
                    ghostClass: 'opacity-50',
                    onStart: () => this.setBuilderState('dragging'),
                    onEnd: () => {
                        this.setBuilderState('idle');
                        this.handleStructureChange();
                    },
                });

                this.lessonSortables.push(sortable);
            });
        },

        destroySortables() {
            if (this.chapterSortable) {
                this.chapterSortable.destroy();
                this.chapterSortable = null;
            }

            this.lessonSortables.forEach((sortable) => sortable.destroy());
            this.lessonSortables = [];
        },

        setBuilderState(state) {
            const root = this.$el.closest('[data-builder-root]');
            if (root) {
                root.setAttribute('data-state', state);
            }
        },

        handleStructureChange() {
            const structure = Array.from(this.$el.querySelectorAll('[data-chapter-item]')).map((chapterEl) => {
                const lessons = Array.from(
                    chapterEl.querySelectorAll('[data-sortable-lessons] [data-lesson-item]')
                ).map((lessonEl) => ({
                    id: Number(lessonEl.dataset.lessonId),
                }));

                return {
                    id: Number(chapterEl.dataset.chapterId),
                    lessons,
                };
            });

            window.Livewire?.dispatch('builder-reorder', { chapters: structure });
        },
    };
}


