<div class="space-y-6">
    @php
        $studentGuides = $guideContext['cards'] ?? [];
    @endphp
    @if(!empty($studentGuides))
        <x-help.contextual-panel
            :guides="$studentGuides"
            :title="$guideContext['title'] ?? __('dashboard.student.guide_title')"
            :subtitle="$guideContext['subtitle'] ?? null" />
    @endif
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.metrics.progress') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $stats['percent'] }}%</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.student.metrics.lessons', ['completed' => $stats['completed'], 'total' => $stats['total']]) }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.metrics.study_time') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $stats['watch_minutes'] }} min</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.student.metrics.logged_in') }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.metrics.xp') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ number_format($gamification['xp']) }}</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.student.metrics.micro') }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.metrics.streak') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $gamification['streak'] }} 🔥</p>
            <p class="text-sm text-slate-500">
                @if($gamification['last_completion'])
                    {{ __('dashboard.student.metrics.streak_hint.last', ['date' => $gamification['last_completion']]) }}
                @else
                    {{ __('dashboard.student.metrics.streak_hint.empty') }}
                @endif
            </p>
        </div>
    </div>

    @if($packReminder)
        <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-5 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-amber-600 tracking-wide">{{ __('student.browser.recommended_pack') }}</p>
                <p class="text-sm font-semibold text-slate-900">
                    {{ $packReminder['practice_title'] }}
                    @if($packReminder['start_at'])
                        · {{ $packReminder['start_at']->translatedFormat('d M H:i') }}
                    @endif
                </p>
                <p class="text-xs text-slate-600">
                    {{ $packReminder['pack']['title'] }} · {{ $packReminder['pack']['sessions'] }} {{ __('student.browser.sessions') }}
                    @if($packReminder['pack']['price_amount'])
                        · ${{ number_format($packReminder['pack']['price_amount'], 0) }} {{ $packReminder['pack']['currency'] }}
                    @endif
                    @if($packReminder['pack']['price_per_session'])
                        {{ __('student.browser.price_per_session', ['price' => number_format($packReminder['pack']['price_per_session'], 1)]) }}
                    @endif
                </p>
                <p class="text-[11px] text-amber-700 font-semibold mt-1">
                    {{ $packReminder['pack']['requires_package']
                        ? __('student.browser.requires_pack')
                        : __('student.browser.activate_pack_hint') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($packReminder['practice_url'])
                    <a href="{{ $packReminder['practice_url'] }}"
                       class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        {{ __('student.browser.view_practices') }} ↗
                    </a>
                @endif
                @if($packReminder['packs_url'])
                    <a href="{{ $packReminder['packs_url'] }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-400 hover:text-blue-600">
                        {{ __('student.packs.view_packs') }} ↗
                    </a>
                @endif
                <button type="button"
                        wire:click="dismissPackReminder"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-400">
                    {{ __('student.browser.dismiss') }}
                </button>
            </div>
        </div>
    @endif

    <livewire:student.discord-practice-browser />

    @if(session('certificate_status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('certificate_status') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.celebrations.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.student.celebrations.subtitle') }}</h4>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ __('dashboard.student.celebrations.events', ['count' => $gamificationFeed->count()]) }}</span>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($gamificationFeed as $event)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ data_get($event->metadata, 'badge') ?: __('dashboard.student.celebrations.badge_fallback') }}</p>
                        <p class="text-xs text-slate-500">
                            {{ optional($event->lesson)->config['title'] ?? __('dashboard.student.celebrations.lesson_fallback') }}
                            · {{ optional($event->created_at)->diffForHumans() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-emerald-600">+{{ $event->points }} XP</p>
                        <p class="text-xs text-slate-400">{{ __('dashboard.student.celebrations.streak', ['count' => data_get($event->metadata, 'streak', $gamification['streak'])]) }}</p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.student.celebrations.empty') }}
                </div>
            @endforelse
        </div>
    </div>

    <livewire:student.practice-packages-catalog
        :highlight-package-id="$highlightPackageId"
        :auto-open-highlight="$autoOpenHighlight" />

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.assignments.student_title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.assignments.student_subtitle') }}</h4>
                <p class="text-xs text-slate-500">{{ __('dashboard.assignments.student_hint') }}</p>
            </div>
        </div>
        @php
            $whatsappSummaryLink = \App\Support\Integrations\WhatsAppLink::assignmentSummary(
                $assignmentSummary,
                $course?->slug,
                'student.dashboard.summary',
                ['course' => $course?->slug]
            );
        @endphp
        @if(!empty($whatsappSummaryLink))
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
                    $whatsLink = \App\Support\Integrations\WhatsAppLink::assignment(
                        [
                            'title' => $assignment['title'],
                            'status' => $assignment['status'] ?? 'pending',
                        ],
                        'student.dashboard.assignment',
                        ['assignment' => $assignment['title']]
                    );
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
        @php
            $resumeLabel = null;
            if ($resumeLesson) {
                $resumeLabel = data_get($resumeLesson->config, 'title');
                $resumeLabel ??= __('dashboard.student.course.lesson_fallback', ['position' => $resumeLesson->position]);
            }
        @endphp
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.course.label') }}</p>
                <h3 class="text-2xl font-semibold text-slate-900">{{ $course->slug }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('dashboard.student.course.description') }}</p>
            </div>
            @if($resumeLesson && $resumeLabel)
                <a href="{{ route('lessons.player', $resumeLesson) }}"
                   class="inline-flex items-center px-5 py-3 rounded-full bg-blue-600 text-white font-semibold shadow-sm hover:bg-blue-700 transition">
                    {{ __('dashboard.student.course.resume', ['title' => $resumeLabel]) }}
                </a>
            @endif
            <div class="flex flex-col items-start gap-2">
                @if($canGenerateCertificate)
                    <button wire:click="generateCertificate" type="button" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 px-4 py-2 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                        🎓 {{ __('dashboard.student.course.generate_certificate') }}
                    </button>
                @endif
                @if($latestCertificate && $certificateDownloadUrl)
                    <a href="{{ $certificateDownloadUrl }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        {{ __('dashboard.student.course.download_certificate') }} ↗
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.student.course.route_label') }}</p>
                    <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.student.course.route_title') }}</h4>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingLessons as $lesson)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                {{ data_get($lesson->config, 'title', __('dashboard.student.course.lesson_fallback', ['position' => $lesson->position])) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ __('dashboard.student.course.chapter', ['position' => $lesson->chapter?->position ?? '—']) }}
                                · {{ __('dashboard.student.course.type', ['type' => ucfirst($lesson->type)]) }}
                            </p>
                        </div>
                        <a href="{{ route('lessons.player', $lesson) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                            {{ __('dashboard.student.course.view') }}
                        </a>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-slate-500 space-y-1">
                        <p class="font-semibold text-slate-900">{{ __('dashboard.student.course.empty_lessons_title') }}</p>
                        <p>{{ __('dashboard.student.course.empty_lessons_description') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-8 text-center space-y-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('dashboard.student.course.no_courses_title') }}</h3>
            <p class="text-sm text-slate-500">{{ __('dashboard.student.course.no_courses_description') }}</p>
        </div>
    @endif
</div>
