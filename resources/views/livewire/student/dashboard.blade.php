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

    @if(session('certificate_status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('certificate_status') }}
        </div>
    @endif

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

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.assignments.student_title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.assignments.student_subtitle') }}</h4>
                <p class="text-xs text-slate-500">{{ __('dashboard.assignments.student_hint') }}</p>
            </div>
        </div>
        @php
            $whatsappSummaryLink = \App\Support\Integrations\WhatsAppLink::assignmentSummary($assignmentSummary, $course?->slug);
        @endphp
        @if($whatsappSummaryLink)
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50/60">
                <a href="{{ $whatsappSummaryLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1 text-[11px] font-semibold text-emerald-700 hover:border-emerald-300">
                    {{ __('whatsapp.assignment.summary_cta') }} <span aria-hidden="true">↗</span>
                </a>
            </div>
        @endif
        @if(array_sum($assignmentSummary) > 0)
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.assignments.summary.title') }}</p>
                <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-700">
                        ⏳ {{ __('dashboard.assignments.summary.pending', ['count' => $assignmentSummary['pending']]) }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">
                        📤 {{ __('dashboard.assignments.summary.submitted', ['count' => $assignmentSummary['submitted']]) }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                        ✅ {{ __('dashboard.assignments.summary.approved', ['count' => $assignmentSummary['approved']]) }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-700">
                        ⚠️ {{ __('dashboard.assignments.summary.rejected', ['count' => $assignmentSummary['rejected']]) }}
                    </span>
                </div>
            </div>
        @endif
        <div class="divide-y divide-slate-100">
            @forelse($upcomingAssignments as $assignment)
                @php
                    $status = $assignment['status'] ?? 'pending';
                    $statusLabel = match ($status) {
                        'approved' => __('dashboard.assignments.status.approved'),
                        'graded' => __('dashboard.assignments.status.graded'),
                        'submitted' => __('dashboard.assignments.status.submitted'),
                        'rejected' => __('dashboard.assignments.status.rejected'),
                        default => __('dashboard.assignments.status.pending'),
                    };
                    $badgeClasses = match ($status) {
                        'approved', 'graded' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'submitted' => 'bg-amber-50 text-amber-700 border border-amber-100',
                        'rejected' => 'bg-rose-50 text-rose-700 border border-rose-100',
                        default => 'bg-slate-50 text-slate-600 border border-slate-100',
                    };
                    $whatsLink = \App\Support\Integrations\WhatsAppLink::assignment($assignment);
                @endphp
                <div class="px-6 py-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $assignment['title'] }}</p>
                        <p class="text-xs text-slate-500">
                            @if($assignment['due_at'])
                                {{ __('dashboard.assignments.due', ['time' => $assignment['due_at']->diffForHumans()]) }}
                            @else
                                {{ __('dashboard.assignments.no_due') }}
                            @endif
                            @if($assignment['requires_approval'])
                                · {{ __('dashboard.assignments.requires_approval') }}
                            @endif
                        </p>
                        @if($assignment['requires_approval'])
                            <p class="text-[11px] text-amber-600 font-semibold">
                                {{ __('dashboard.assignments.minimum_score', ['score' => $assignment['passing_score']]) }}
                            </p>
                        @endif
                        @if($assignment['feedback'])
                            <p class="text-[11px] text-rose-600 mt-1">{{ $assignment['feedback'] }}</p>
                        @endif
                    </div>
                    <div class="text-right space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">
                            {{ $statusLabel }}
                            @if($assignment['score'])
                                · {{ $assignment['score'] }} pts
                            @endif
                        </span>
                        @if($whatsLink)
                            <a href="{{ $whatsLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-300">
                                {{ __('whatsapp.assignment.help_cta') }} ↗
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-4 text-center text-sm text-slate-500">
                    {{ __('dashboard.assignments.empty') }}
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
            <div class="flex flex-col items-start gap-2">
                @if($canGenerateCertificate)
                    <button wire:click="generateCertificate" type="button" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 px-4 py-2 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                        🎓 Generar certificado
                    </button>
                @endif
                @if($latestCertificate && $certificateDownloadUrl)
                    <a href="{{ $certificateDownloadUrl }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Descargar certificado ↗
                    </a>
                @endif
            </div>
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
