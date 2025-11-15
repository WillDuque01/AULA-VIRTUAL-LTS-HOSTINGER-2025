<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Progreso</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $stats['percent'] }}%</p>
            <p class="text-sm text-slate-500">{{ $stats['completed'] }} / {{ $stats['total'] }} lecciones</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Tiempo de estudio</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $stats['watch_minutes'] }} min</p>
            <p class="text-sm text-slate-500">Registrados en tus sesiones</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">XP acumulado</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ number_format($gamification['xp']) }}</p>
            <p class="text-sm text-slate-500">Microinteracciones activas</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Racha</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $gamification['streak'] }} 🔥</p>
            <p class="text-sm text-slate-500">
                @if($gamification['last_completion'])
                    Última: {{ $gamification['last_completion'] }}
                @else
                    Completa una lección para iniciar racha
                @endif
            </p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Celebraciones recientes</p>
                <h4 class="text-lg font-semibold text-slate-900">Últimos logros</h4>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ $gamificationFeed->count() }} eventos</span>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($gamificationFeed as $event)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ data_get($event->metadata, 'badge') ?: '✅ Lección completada' }}</p>
                        <p class="text-xs text-slate-500">
                            {{ optional($event->lesson)->config['title'] ?? __('Lección') }}
                            · {{ optional($event->created_at)->diffForHumans() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-emerald-600">+{{ $event->points }} XP</p>
                        <p class="text-xs text-slate-400">Racha {{ data_get($event->metadata, 'streak', $gamification['streak']) }}</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    Completa tu próxima lección para desbloquear celebraciones y XP.
                </div>
            @endforelse
        </div>
    </div>

    @if($course)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Curso actual</p>
                <h3 class="text-2xl font-semibold text-slate-900">{{ $course->slug }}</h3>
                <p class="text-sm text-slate-500 mt-1">Continúa exactamente donde lo dejaste.</p>
            </div>
            @if($resumeLesson)
                <a href="{{ route('lessons.player', $resumeLesson) }}"
                   class="inline-flex items-center px-5 py-3 rounded-full bg-blue-600 text-white font-semibold shadow-sm hover:bg-blue-700 transition">
                    Reanudar “{{ data_get($resumeLesson->config, 'title', 'Lección '.$resumeLesson->position) }}”
                </a>
            @endif
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Tu ruta</p>
                    <h4 class="text-lg font-semibold text-slate-900">Próximas lecciones</h4>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingLessons as $lesson)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ data_get($lesson->config, 'title', 'Lección '.$lesson->position) }}</p>
                            <p class="text-xs text-slate-500">Capítulo {{ $lesson->chapter?->position ?? '—' }} · Tipo {{ ucfirst($lesson->type) }}</p>
                        </div>
                        <a href="{{ route('lessons.player', $lesson) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver</a>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-slate-500">
                        ¡Todo listo! No tienes lecciones pendientes en este curso.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-8 text-center space-y-3">
            <h3 class="text-lg font-semibold text-slate-900">Aún no tienes cursos asignados</h3>
            <p class="text-sm text-slate-500">Cuando se publique un curso para ti, aparecerá aquí con métricas y ruta sugerida.</p>
        </div>
    @endif
</div>
