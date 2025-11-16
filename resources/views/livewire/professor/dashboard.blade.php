<div class="space-y-6">
    @php
        $profGuides = $guideContext['cards'] ?? [];
        $teacherPlaybook = $integrationPlaybook ?? [];
    @endphp
    @if(!empty($profGuides))
        <x-help.contextual-panel
            :guides="$profGuides"
            :title="$guideContext['title'] ?? __('Guía rápida')"
            :subtitle="$guideContext['subtitle'] ?? null" />
    @endif
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Estudiantes activos (7d)</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['active_students'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Progreso nuevo (7d)</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['recent_updates'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Completitud promedio</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['avg_completion'] }}%</p>
        </div>
    </div>

    @if(!empty($submissionStats))
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="bg-white border border-amber-200 rounded-2xl p-4 shadow-sm">
                <p class="text-xs uppercase font-semibold text-amber-600 tracking-wide">{{ __('Propuestas pendientes') }}</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $submissionStats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white border border-emerald-200 rounded-2xl p-4 shadow-sm">
                <p class="text-xs uppercase font-semibold text-emerald-600 tracking-wide">{{ __('Aprobadas (7d)') }}</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $submissionStats['approved_7d'] ?? 0 }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-2xl p-4 shadow-sm">
                <p class="text-xs uppercase font-semibold text-rose-600 tracking-wide">{{ __('Rechazadas (7d)') }}</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $submissionStats['rejected_7d'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('Revisión de contenido docente') }}</p>
                    <p class="text-sm text-slate-500">{{ __('Resumen de propuestas recientes y tendencia (7 días).') }}</p>
                </div>
                <a href="{{ route('admin.teacher-submissions', ['locale' => app()->getLocale()]) }}"
                   class="text-xs font-semibold text-blue-600 hover:underline">
                    {{ __('Abrir bandeja') }} →
                </a>
            </div>
            <div class="px-6 py-5 grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    @forelse($submissionFeed ?? collect() as $submission)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $submission->title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst($submission->type) }} ·
                                {{ $submission->author?->name ?? $submission->author?->email ?? __('Docente') }}
                                @if($submission->course)
                                    · {{ $submission->course->slug }}
                                @endif
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5
                                    @class([
                                        'border border-amber-200 bg-amber-50 text-amber-700' => $submission->status === 'pending',
                                        'border border-emerald-200 bg-emerald-50 text-emerald-700' => $submission->status === 'approved',
                                        'border border-rose-200 bg-rose-50 text-rose-700' => $submission->status === 'rejected',
                                    ])
                                ">
                                    {{ ucfirst(__($submission->status)) }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 text-slate-500">
                                    {{ optional($submission->created_at)->format('d M H:i') }}
                                </span>
                            </div>
                            @if($submission->feedback && $submission->status !== 'pending')
                                <p class="mt-2 text-xs text-slate-500">{{ __('Feedback: :feedback', ['feedback' => $submission->feedback]) }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No hay propuestas recientes.') }}</p>
                    @endforelse
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-xs uppercase font-semibold text-slate-500">{{ __('Tendencia semanal') }}</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 max-h-64 overflow-y-auto">
                        @forelse($submissionTrend ?? collect() as $entry)
                            <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                <span class="text-xs font-semibold text-slate-500">{{ $entry['day'] }}</span>
                                <div class="flex items-center gap-3 text-[11px] font-semibold">
                                    <span class="text-slate-600">{{ __('E: :count', ['count' => $entry['submitted']]) }}</span>
                                    <span class="text-emerald-600">{{ __('A: :count', ['count' => $entry['approved']]) }}</span>
                                    <span class="text-rose-600">{{ __('R: :count', ['count' => $entry['rejected']]) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">{{ __('Sin datos suficientes todavía.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($teacherPlaybook))
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-1">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('Integraciones críticas para Teacher Admin') }}</p>
                <p class="text-sm text-slate-500">{{ __('Asegúrate de que los bots y CTA estén listos antes de agendar cohortes.') }}</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($teacherPlaybook as $group)
                    @foreach($group['items'] as $item)
                        <div class="px-6 py-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                                @if(!empty($item['status_hint']))
                                    <p class="text-xs text-slate-500">{{ $item['status_hint'] }}</p>
                                @endif
                                @if(!empty($item['next_steps']))
                                    <ul class="mt-1 list-disc space-y-1 pl-4 text-xs text-slate-500">
                                        @foreach($item['next_steps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $item['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $item['status'] }}
                                </span>
                                @if(!empty($item['docs']))
                                    <a href="{{ $item['docs'] }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-full border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-700 hover:border-blue-300">
                                        {{ __('Ver docs') }} ↗
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Prácticas Discord</p>
                <h4 class="text-lg font-semibold text-slate-900">Slots y solicitudes</h4>
            </div>
            <a href="{{ route('professor.discord-practices', ['locale' => app()->getLocale()]) }}"
               class="text-xs font-semibold text-blue-600 hover:underline">
                Ver planificador →
            </a>
        </div>
        <div class="px-6 py-5 grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs uppercase text-slate-500">Próximas</p>
                <p class="text-3xl font-bold text-slate-900">{{ $practiceStats['upcoming'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Reservas</p>
                <p class="text-3xl font-bold text-emerald-600">{{ $practiceStats['slots_filled'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Solicitudes</p>
                <p class="text-3xl font-bold text-amber-500">{{ $practiceStats['requests'] ?? 0 }}</p>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($upcomingPractices ?? collect() as $practice)
                <div class="px-6 py-4 flex flex-col gap-1 text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">{{ $practice['title'] }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $practice['course'] }} · {{ $practice['lesson'] }} · {{ $practice['start_at']->format('d M H:i') }}
                        ({{ ucfirst($practice['type']) }}{{ $practice['cohort'] ? ' · '.$practice['cohort'] : '' }})
                    </p>
                    <p class="text-xs text-slate-400">Capacidad {{ $practice['reserved'] }} / {{ $practice['capacity'] }}</p>
                </div>
            @empty
                <div class="px-6 py-4 text-sm text-slate-500">
                    Aún no hay sesiones programadas en el calendario.
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.whatsapp.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.whatsapp.subtitle') }}</h4>
            </div>
        </div>
        <div class="px-6 py-5 space-y-3">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.today') }}</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $whatsappStats['today'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.week') }}</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $whatsappStats['week'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.contexts') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        @forelse(collect($whatsappStats['contexts'] ?? []) as $context)
                            <li class="flex items-center justify-between">
                                <span>{{ $context['context'] }}</span>
                                <span class="font-semibold">{{ $context['count'] }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-slate-400">{{ __('dashboard.whatsapp.empty') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('prof.dashboard.heatmap.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ $heatmap['lesson'] ?? __('prof.dashboard.heatmap.empty_title') }}</h4>
                @if($heatmap['lesson'])
                    <p class="text-xs text-slate-500">{{ $heatmap['course'] }}</p>
                @endif
            </div>
        </div>
        <div class="px-6 py-6">
            @if(!empty($heatmap['segments']))
                @php
                    $maxReach = max(array_column($heatmap['segments'], 'reach')) ?: 1;
                @endphp
                <div class="flex items-end gap-1 h-48">
                    @foreach($heatmap['segments'] as $segment)
                        @php
                            $height = max(6, ($segment['reach'] / $maxReach) * 100);
                        @endphp
                        <div class="flex flex-col items-center gap-2 w-6">
                            <div class="w-full rounded-full bg-sky-400/50" style="height: {{ $height }}%;"></div>
                            <span class="text-[10px] text-slate-500">{{ $segment['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mt-4">
                    {{ __('prof.dashboard.heatmap.hint', ['seconds' => $heatmap['bucket_seconds']]) }}
                </p>
            @else
                <p class="text-sm text-slate-500">{{ __('prof.dashboard.heatmap.empty_state') }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Insights</p>
                <h4 class="text-lg font-semibold text-slate-900">Lecciones con mejor desempeño</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($lessonInsights as $insight)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ data_get($insight, 'lesson.config.title', 'Lección '.$insight['lesson']->position) }}</p>
                        <p class="text-xs text-slate-500">
                            {{ data_get($insight, 'course.slug', 'curso') }} · {{ $insight['viewers'] }} estudiantes
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-slate-900">{{ $insight['avg_completion'] }}%</p>
                        <p class="text-xs text-slate-500">Prom. completado</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    Aún no hay datos suficientes. Pide a tus estudiantes que reproduzcan sus lecciones.
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Actividad</p>
                <h4 class="text-lg font-semibold text-slate-900">Últimos eventos</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentActivity as $progress)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">
                            {{ data_get($progress->lesson->config, 'title', 'Lección '.$progress->lesson->position) }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Actualizado {{ $progress->updated_at?->diffForHumans() }}
                        </p>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">{{ round($progress->watched_seconds / max(1, (int) data_get($progress->lesson->config, 'length', 1)) * 100) }}%</p>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    Sin actividad reciente.
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.certificates.recent') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.certificates.title') }}</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentCertificates as $certificate)
                <div class="px-6 py-4 flex items-center justify-between text-sm">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $certificate->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $certificate->course?->slug }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('dashboard.certificates.verified_label', ['count' => $certificate->verified_count]) }}</p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <p>{{ optional($certificate->issued_at)->diffForHumans() }}</p>
                        <p class="font-mono text-slate-400">{{ $certificate->code }}</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.certificates.empty') }}
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.assignments.professor_title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.assignments.professor_subtitle') }}</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($assignmentAlerts as $assignment)
                <div class="px-6 py-4 flex flex-col gap-2 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-900">{{ $assignment['title'] }}</p>
                            @if($assignment['requires_approval'])
                                <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                    🛡️ {{ __('dashboard.assignments.professor_requires_approval') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">
                            @if($assignment['due_at'])
                                {{ $assignment['due_at']->diffForHumans() }} ·
                            @endif
                            {{ $assignment['course'] }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px] font-semibold justify-end">
                        @if(($assignment['pending'] ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">
                                ⏳ {{ __('dashboard.assignments.professor_pending_chip', ['count' => $assignment['pending']]) }}
                            </span>
                        @endif
                        @if(($assignment['rejected'] ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-rose-700">
                                ⚠️ {{ __('dashboard.assignments.professor_rejected_chip', ['count' => $assignment['rejected']]) }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                            ✅ {{ __('dashboard.assignments.professor_approved_chip', ['count' => $assignment['approved'] ?? 0]) }}
                        </span>
                        @php($profWhats = \App\Support\Integrations\WhatsAppLink::assignment(
                            [
                                'title' => $assignment['title'],
                                'status' => ($assignment['pending'] ?? 0) > 0 ? 'pending' : 'approved',
                            ],
                            'professor.dashboard.assignments',
                            ['assignment_id' => $assignment['id'] ?? null]
                        ))
                        @if($profWhats)
                            <a href="{{ $profWhats }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-600 hover:border-slate-400">
                                {{ __('whatsapp.assignment.followup_cta') }} ↗
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.assignments.empty') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
