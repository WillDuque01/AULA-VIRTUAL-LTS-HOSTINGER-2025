<div class="space-y-6">
    @php
        $profGuides = $guideContext['cards'] ?? []; // [AGENTE: GPT-5.1 CODEX] - Guías rápidas para el panel
        $teacherPlaybook = $integrationPlaybook ?? []; // [AGENTE: GPT-5.1 CODEX] - Integraciones clave disponibles
        $teacherUser = auth()->user(); // [AGENTE: GPT-5.1 CODEX] - Usuario autenticado para personalizar el saludo
        $localizedNow = now()->setTimezone($teacherUser?->timezone ?? config('app.timezone')); // [AGENTE: GPT-5.1 CODEX] - Fecha/hora ajustada al timezone del docente
        $currentHour = (int) $localizedNow->format('H'); // [AGENTE: GPT-5.1 CODEX] - Hora actual para elegir el saludo
        $greetingLabel = match (true) { // [AGENTE: GPT-5.1 CODEX] - Selecciona saludo contextual
            $currentHour < 12 => __('dashboard.professor.greetings.morning'),
            $currentHour < 19 => __('dashboard.professor.greetings.afternoon'),
            default => __('dashboard.professor.greetings.evening'),
        }; // [AGENTE: GPT-5.1 CODEX] - Cierre del saludo contextual
        $shortTeacherName = trim(explode(' ', $teacherUser?->name ?? __('dashboard.professor.fallback_name'))[0] ?? __('dashboard.professor.fallback_name')); // [AGENTE: GPT-5.1 CODEX] - Extrae primer nombre para el mensaje
    @endphp
    @if(!empty($profGuides))
        <x-help.contextual-panel
            :guides="$profGuides"
            :title="$guideContext['title'] ?? __('dashboard.professor.guide_title')"
            :subtitle="$guideContext['subtitle'] ?? null" />
    @endif
    <div class="rounded-3xl border border-slate-100 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 shadow-2xl shadow-slate-900/30 text-white" {{-- // [AGENTE: GPT-5.1 CODEX] - Banner de bienvenida estilo UIX 2030 --}}
         x-data="{ pulse: false }" x-init="setTimeout(() => { pulse = true }, 300)"> {{-- // [AGENTE: GPT-5.1 CODEX] - Microanimación inicial --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" {{-- // [AGENTE: GPT-5.1 CODEX] - Layout responsivo para el saludo --}}
        >
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-300">{{ __('dashboard.professor.banner.label') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Meta etiqueta --}}
                <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $greetingLabel }}, {{ $shortTeacherName }} 👋</h1> {{-- // [AGENTE: GPT-5.1 CODEX] - Saludo personalizado --}}
                <p class="mt-2 text-sm text-slate-200">{{ __('dashboard.professor.banner.description') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Texto contextual --}}
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-semibold backdrop-blur" {{-- // [AGENTE: GPT-5.1 CODEX] - Indicador de hora local --}}
            >
                <p class="text-xs uppercase tracking-wide text-slate-200">{{ __('dashboard.professor.banner.local_time') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Etiqueta --}}
                <p class="text-lg font-mono">{{ $localizedNow->format('H:i') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Hora en formato 24h --}}
            </div>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-xl shadow-slate-200/60" {{-- // [AGENTE: GPT-5.1 CODEX] - Tarjeta métrica UIX 2030 --}}
             x-data="animatedCount({{ (int) ($metrics['active_students'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] - Cuenta animada --}}
            <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('dashboard.professor.metrics.active_students') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Etiqueta --}}
            <p class="mt-2 text-3xl font-semibold text-slate-900" x-text="display"></p> {{-- // [AGENTE: GPT-5.1 CODEX] - Valor animado --}}
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-xl shadow-slate-200/60" {{-- // [AGENTE: GPT-5.1 CODEX] - Tarjeta métrica --}}
             x-data="animatedCount({{ (int) ($metrics['recent_updates'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] - Animación --}}
            <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('dashboard.professor.metrics.recent_progress') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            <p class="mt-2 text-3xl font-semibold text-slate-900" x-text="display"></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-xl shadow-slate-200/60" {{-- // [AGENTE: GPT-5.1 CODEX] --}}
             x-data="animatedCount({{ (int) ($metrics['avg_completion'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('dashboard.professor.metrics.avg_completion') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            <p class="mt-2 text-3xl font-semibold text-slate-900"><span x-text="display"></span>%</p> {{-- // [AGENTE: GPT-5.1 CODEX] - Valor con porcentaje --}}
        </div>
    </div>

    @if(!empty($submissionStats))
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-amber-200 bg-amber-50/70 p-4 shadow-lg shadow-amber-200/40" {{-- // [AGENTE: GPT-5.1 CODEX] - Tarjeta estado pendientes --}}
                 x-data="animatedCount({{ (int) ($submissionStats['pending'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-amber-700">{{ __('dashboard.professor.metrics.pending_proposals') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="mt-2 text-3xl font-semibold text-amber-900" x-text="display"></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            </div>
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-lg shadow-emerald-200/40" {{-- // [AGENTE: GPT-5.1 CODEX] - Aprobadas --}}
                 x-data="animatedCount({{ (int) ($submissionStats['approved_7d'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-emerald-700">{{ __('dashboard.professor.metrics.approved') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="mt-2 text-3xl font-semibold text-emerald-900" x-text="display"></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            </div>
            <div class="rounded-3xl border border-rose-200 bg-rose-50/70 p-4 shadow-lg shadow-rose-200/40" {{-- // [AGENTE: GPT-5.1 CODEX] - Rechazadas --}}
                 x-data="animatedCount({{ (int) ($submissionStats['rejected_7d'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-[11px] uppercase font-semibold tracking-[0.35em] text-rose-700">{{ __('dashboard.professor.metrics.rejected') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="mt-2 text-3xl font-semibold text-rose-900" x-text="display"></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.professor.reviews.title') }}</p>
                    <p class="text-sm text-slate-500">{{ __('dashboard.professor.reviews.subtitle') }}</p>
                </div>
                <a href="{{ route('admin.teacher-submissions', ['locale' => app()->getLocale()]) }}"
                   class="text-xs font-semibold text-blue-600 hover:underline">
                    {{ __('dashboard.professor.reviews.link') }} →
                </a>
            </div>
            <div class="px-6 py-5 grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    @forelse($submissionFeed ?? collect() as $submission)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $submission->title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst($submission->type) }} ·
                                {{ $submission->author?->name ?? $submission->author?->email ?? __('dashboard.professor.fallback_name') }}
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
                                <p class="mt-2 text-xs text-slate-500">{{ __('dashboard.professor.reviews.feedback', ['feedback' => $submission->feedback]) }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('dashboard.professor.reviews.empty') }}</p>
                    @endforelse
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <p class="text-xs uppercase font-semibold text-slate-500">{{ __('dashboard.professor.reviews.trend_title') }}</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600 max-h-64 overflow-y-auto">
                        @forelse($submissionTrend ?? collect() as $entry)
                            <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                <span class="text-xs font-semibold text-slate-500">{{ $entry['day'] }}</span>
                                <div class="flex items-center gap-3 text-[11px] font-semibold">
                                    <span class="text-slate-600">{{ __('dashboard.professor.reviews.trend_labels.submitted', ['count' => $entry['submitted']]) }}</span>
                                    <span class="text-emerald-600">{{ __('dashboard.professor.reviews.trend_labels.approved', ['count' => $entry['approved']]) }}</span>
                                    <span class="text-rose-600">{{ __('dashboard.professor.reviews.trend_labels.rejected', ['count' => $entry['rejected']]) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">{{ __('dashboard.professor.reviews.trend_empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($teacherPlaybook))
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-1">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.professor.playbook.title') }}</p>
                <p class="text-sm text-slate-500">{{ __('dashboard.professor.playbook.subtitle') }}</p>
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
                                        {{ __('dashboard.professor.playbook.docs') }} ↗
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
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.professor.practices_panel.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.professor.practices_panel.subtitle') }}</h4>
            </div>
            <a href="{{ route('professor.discord-practices', ['locale' => app()->getLocale()]) }}"
               class="text-xs font-semibold text-blue-600 hover:underline">
                {{ __('dashboard.professor.practices_panel.cta') }}
            </a>
        </div>
        <div class="px-6 py-5 grid gap-4 sm:grid-cols-3">
            <div x-data="animatedCount({{ (int) ($practiceStats['upcoming'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] - Tarjeta estadísticas próximas --}}
                <p class="text-xs uppercase text-slate-500">{{ __('dashboard.professor.practices_panel.upcoming') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-3xl font-bold text-slate-900"><span x-text="display"></span></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            </div>
            <div x-data="animatedCount({{ (int) ($practiceStats['slots_filled'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] - Reservas --}}
                <p class="text-xs uppercase text-slate-500">{{ __('dashboard.professor.practices_panel.reservations') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-3xl font-bold text-emerald-600"><span x-text="display"></span></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
            </div>
            <div x-data="animatedCount({{ (int) ($practiceStats['requests'] ?? 0) }})"> {{-- // [AGENTE: GPT-5.1 CODEX] - Solicitudes --}}
                <p class="text-xs uppercase text-slate-500">{{ __('dashboard.professor.practices_panel.requests') }}</p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
                <p class="text-3xl font-bold text-amber-500"><span x-text="display"></span></p> {{-- // [AGENTE: GPT-5.1 CODEX] --}}
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
                    <p class="text-xs text-slate-400">{{ __('dashboard.professor.practices_panel.capacity', ['reserved' => $practice['reserved'], 'capacity' => $practice['capacity']]) }}</p>
                </div>
            @empty
                <div class="px-6 py-4 text-sm text-slate-500">
                    {{ __('dashboard.professor.practices_panel.empty') }}
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
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.professor.insights.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.professor.insights.subtitle') }}</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($lessonInsights as $insight)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ data_get($insight, 'lesson.config.title', __('dashboard.professor.insights.lesson_placeholder', ['position' => $insight['lesson']->position])) }}</p>
                        <p class="text-xs text-slate-500">
                            {{ data_get($insight, 'course.slug', __('dashboard.professor.insights.course_placeholder')) }} · {{ __('dashboard.professor.insights.students_label', ['count' => $insight['viewers']]) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-slate-900">{{ $insight['avg_completion'] }}%</p>
                        <p class="text-xs text-slate-500">{{ __('dashboard.professor.insights.completion') }}</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.professor.insights.empty') }}
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.professor.activity.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.professor.activity.subtitle') }}</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentActivity as $progress)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">
                            {{ data_get($progress->lesson->config, 'title', __('dashboard.professor.lessons.progress_placeholder', ['position' => $progress->lesson->position])) }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ __('dashboard.professor.activity.updated', ['time' => $progress->updated_at?->diffForHumans()]) }}
                        </p>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">{{ round($progress->watched_seconds / max(1, (int) data_get($progress->lesson->config, 'length', 1)) * 100) }}%</p>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.professor.activity.empty') }}
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
                        @php
                            $profWhats = \App\Support\Integrations\WhatsAppLink::assignment(
                                [
                                    'title' => $assignment['title'],
                                    'status' => ($assignment['pending'] ?? 0) > 0 ? 'pending' : 'approved',
                                ],
                                'professor.dashboard.assignments',
                                ['assignment_id' => $assignment['id'] ?? null]
                            );
                        @endphp
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


