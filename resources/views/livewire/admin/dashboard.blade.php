<div class="space-y-6">
    @php
        $adminGuides = $guideContext['cards'] ?? [];
        $playbookGroups = $integrationPlaybook ?? [];
    @endphp
    @if(!empty($adminGuides))
        <x-help.contextual-panel
            :guides="$adminGuides"
            :title="$guideContext['title'] ?? __('dashboard.admin.guide_title')"
            :subtitle="$guideContext['subtitle'] ?? null" />
    @endif
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.metrics.users') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['users'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.metrics.subscriptions') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['active_subscriptions'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.metrics.mrr') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">${{ number_format($metrics['mrr'], 2) }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.metrics.watch_hours') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['watch_hours'] }}</p>
        </div>
        <div class="bg-white border border-indigo-200 rounded-2xl p-4 shadow-sm sm:col-span-2 lg:col-span-1">
            <p class="text-xs uppercase font-semibold text-indigo-600 tracking-wide">{{ __('dashboard.admin.reviews.title') }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $pendingApprovals['submissions'] }}</p>
            <p class="text-xs text-slate-500">{{ __('dashboard.admin.reviews.subtitle') }}</p>
            <div class="mt-4 space-y-2 text-[11px] font-semibold text-slate-500">
                @foreach(['modules', 'lessons', 'packs'] as $key)
                    @php
                        $label = __('dashboard.admin.content_labels.'.$key);
                        $totals = $contentStatusTotals[$key] ?? [];
                    @endphp
                    <div class="flex flex-col rounded-2xl border border-slate-100 bg-white/60 px-3 py-2">
                        <div class="flex items-center justify-between">
                            <span>{{ $label }}</span>
                            <span class="text-xs text-slate-400">{{ __('dashboard.admin.reviews.pending_status', ['count' => $pendingApprovals[$key] ?? 0]) }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-2 text-[10px]">
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 px-2 py-0.5 text-emerald-700">
                                {{ __('dashboard.admin.reviews.published_chip', ['count' => $totals['published'] ?? 0]) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 px-2 py-0.5 text-amber-700">
                                {{ __('dashboard.admin.reviews.pending_chip', ['count' => $totals['pending'] ?? 0]) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 px-2 py-0.5 text-rose-700">
                                {{ __('dashboard.admin.reviews.rejected_status', ['count' => $totals['rejected'] ?? 0]) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.teacher-submissions', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1.5 text-xs font-semibold text-indigo-700 hover:border-indigo-300">
                    {{ __('dashboard.admin.reviews.inbox_link') }} →
                </a>
                <a href="{{ route('admin.teacher-performance', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300">
                    {{ __('dashboard.admin.reviews.report_link') }} →
                </a>
            </div>
        </div>
    </div>

    @if($teacherBacklog->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.backlog.title') }}</p>
                    <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.admin.backlog.subtitle') }}</h4>
                </div>
                <a href="{{ route('admin.teachers', ['locale' => app()->getLocale()]) }}"
                   class="text-xs font-semibold text-blue-600 hover:underline">
                    {{ __('dashboard.admin.backlog.manage_link') }} →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($teacherBacklog as $entry)
                    <div class="px-6 py-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $entry->author?->name ?? __('dashboard.admin.backlog.placeholder', ['id' => $entry->user_id]) }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $entry->author?->email }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-amber-700">
                                {{ __('dashboard.admin.reviews.pending_status', ['count' => $entry->pending]) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-emerald-700">
                                {{ __('dashboard.admin.reviews.approved_status', ['count' => $entry->approved]) }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-rose-700">
                                {{ __('dashboard.admin.reviews.rejected_status', ['count' => $entry->rejected]) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($approvalTrend->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.trend.title') }}</p>
            </div>
            <div class="px-6 py-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm text-slate-600">
                @foreach($approvalTrend as $entry)
                    <div class="rounded-2xl border border-slate-100 bg-white/70 px-4 py-3">
                        <p class="text-xs uppercase text-slate-400">{{ $entry['day'] }}</p>
                        <div class="mt-2 flex flex-col gap-1 text-[12px] font-semibold">
                            <span class="text-slate-700">{{ __('dashboard.admin.trend.submitted', ['count' => $entry['submitted']]) }}</span>
                            <span class="text-emerald-600">{{ __('dashboard.admin.trend.approved', ['count' => $entry['approved']]) }}</span>
                            <span class="text-rose-600">{{ __('dashboard.admin.trend.rejected', ['count' => $entry['rejected']]) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-1">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.whatsapp.title') }}</p>
            <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.whatsapp.subtitle') }}</h4>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.today') }}</p>
                    <p class="text-3xl font-bold text-slate-900">{{ $whatsappStats['today'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.week') }}</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $whatsappStats['week'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">{{ __('dashboard.whatsapp.trend') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        @forelse(collect($whatsappStats['trend'] ?? []) as $entry)
                            <li class="flex items-center justify-between">
                                <span>{{ $entry['day'] }}</span>
                                <span class="font-semibold">{{ $entry['total'] }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-slate-400">{{ __('dashboard.whatsapp.empty') }}</li>
                        @endforelse
                    </ul>
                </div>
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

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.abandonment.title') }}</p>
            <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.abandonment.subtitle') }}</h4>
        </div>
        <div class="p-6">
            @if($abandonmentInsights->isEmpty())
                <p class="text-sm text-slate-500">{{ __('dashboard.abandonment.empty') }}</p>
            @else
                <ul class="space-y-3">
                    @foreach($abandonmentInsights as $insight)
                        <li class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $insight['lesson'] }}</p>
                                <p class="text-xs text-slate-500">{{ $insight['course'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase text-slate-400">{{ __('dashboard.abandonment.timestamp') }}</p>
                                <p class="text-base font-semibold text-slate-900">{{ $insight['timestamp'] }}</p>
                                <p class="text-xs text-slate-500">{{ __('dashboard.abandonment.reach', ['count' => $insight['reach']]) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.gamification.top_xp') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.gamification.xp_title') }}</h4>
            </div>
            <div class="p-6">
                @if($topXpStudents->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('dashboard.gamification.empty') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach($topXpStudents as $student)
                            <li class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-800">{{ $student['name'] }}</span>
                                <span class="text-emerald-600 font-semibold">+{{ number_format($student['xp']) }} XP</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.gamification.top_streaks') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.gamification.streak_title') }}</h4>
            </div>
            <div class="p-6">
                @if($topStreaks->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('dashboard.gamification.empty') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach($topStreaks as $student)
                            <li class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-800">{{ $student['name'] }}</span>
                                <span class="text-sky-600 font-semibold">{{ $student['streak'] }} 🔥</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col gap-2">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.certificates.title') }}</p>
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-xs text-slate-500">{{ __('dashboard.certificates.total') }}</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $certificateStats['total'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">{{ __('dashboard.certificates.last24') }}</p>
                        <p class="text-3xl font-bold text-emerald-600">{{ $certificateStats['last_24h'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">{{ __('dashboard.certificates.verified_total') }}</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $certificateStats['verified_total'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.certificates.recent') }}</p>
            </div>
            <div class="p-6">
                @if($recentCertificates->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('dashboard.certificates.empty') }}</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach($recentCertificates as $certificate)
                            <li class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $certificate['student'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $certificate['course'] }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('dashboard.certificates.verified_label', ['count' => $certificate['verified']]) }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-500">
                                    <p>{{ $certificate['issued_at'] }}</p>
                                    <p class="font-mono text-slate-400">{{ $certificate['code'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.certificates.recent_verifications') }}</p>
            <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.certificates.verifications_subtitle') }}</h4>
        </div>
        <div class="p-6">
            @if($recentVerifications->isEmpty())
                <p class="text-sm text-slate-500">{{ __('dashboard.certificates.verifications_empty') }}</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach($recentVerifications as $verification)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $verification['student'] ?? __('dashboard.certificates.anonymous_student') }}</p>
                                <p class="text-xs text-slate-500">{{ $verification['course'] }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ __('dashboard.certificates.verification_source', ['source' => strtoupper($verification['source'] ?? 'web')]) }}
                                    · IP {{ $verification['ip'] ?? '—' }}
                                </p>
                                <details class="mt-1 text-[11px] text-slate-400">
                                    <summary class="cursor-pointer">{{ __('dashboard.certificates.verification_details') }}</summary>
                                    <p>{{ $verification['user_agent'] ?? 'N/A' }}</p>
                                </details>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <p>{{ $verification['verified_at'] }}</p>
                                <p class="font-mono text-slate-400">{{ $verification['code'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-[0.3em]">{{ __('outbox.title') }}</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('outbox.subtitle') }}</h3>
                <p class="text-xs text-slate-500">
                    {{ __('dashboard.admin.outbox.summary', ['pending' => $outboxStats['pending'], 'failed' => $outboxStats['failed']]) }}
                </p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <p class="text-xs uppercase text-slate-500">{{ __('outbox.status.pending') }}</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $outboxStats['pending'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs uppercase text-slate-500">{{ __('outbox.status.failed') }}</p>
                    <p class="text-2xl font-bold text-rose-600">{{ $outboxStats['failed'] }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                <span>{{ $outboxStats['last_failed_at'] ? $outboxStats['last_failed_at']->diffForHumans() : __('outbox.no_recent_errors') }}</span>
                <a href="{{ route('admin.integrations.outbox') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-700">
                    {{ __('outbox.view_link') }} →
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.revenue.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.admin.revenue.subtitle') }}</h4>
            </div>
            <div class="p-6">
                @if($revenueTrend->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('dashboard.admin.revenue.empty') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach($revenueTrend as $entry)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ $entry['day'] }}</span>
                                <span class="font-semibold text-slate-900">${{ number_format($entry['total'], 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.video.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.admin.video.subtitle') }}</h4>
            </div>
            <div class="p-6">
                @if($watchPerCourse->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('dashboard.admin.video.empty') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach($watchPerCourse as $entry)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ $entry['course'] }}</span>
                                <span class="font-semibold text-slate-900">{{ $entry['hours'] }} h</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.integrations.title') }}</p>
            <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.admin.integrations.subtitle') }}</h4>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($integrationStatus as $key => $status)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $status['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $status['status'] }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $status['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $status['ok'] ? __('dashboard.status.ok') : __('dashboard.status.pending') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    @if(!empty($playbookGroups))
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ __('dashboard.admin.playbook.title') }}</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ __('dashboard.admin.playbook.subtitle') }}</h4>
                <p class="text-xs text-slate-500">{{ __('dashboard.admin.playbook.hint') }}</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($playbookGroups as $group)
                    <div class="px-6 py-3 bg-slate-50/60">
                        <p class="text-[11px] uppercase font-semibold text-slate-500 tracking-[0.3em]">{{ $group['title'] }}</p>
                    </div>
                    @foreach($group['items'] as $item)
                        <div class="px-6 py-4 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                                @if(!empty($item['status_hint']))
                                    <p class="text-xs text-slate-500">{{ $item['status_hint'] }}</p>
                                @endif
                                @if(!empty($item['missing']))
                                    <p class="text-xs text-amber-600 font-semibold">
                                        {{ __('dashboard.admin.playbook.missing', ['vars' => implode(', ', $item['missing'])]) }}
                                    </p>
                                @endif
                                @if(!empty($item['next_steps']))
                                    <ul class="mt-1 list-disc space-y-1 pl-4 text-xs text-slate-500">
                                        @foreach($item['next_steps'] as $step)
                                            <li>{{ $step }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="flex flex-col items-start gap-2 lg:items-end">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $item['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $item['status'] }}
                                </span>
                                @if(!empty($item['tokens']))
                                    <div class="flex flex-wrap gap-2 text-[11px] text-slate-500">
                                        @foreach($item['tokens'] as $token)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                                                {{ $token['label'] ?? __('dashboard.admin.playbook.env') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                @php
                                    $docAnchor = $item['docs'] ?? null;
                                    $docUrl = $docAnchor
                                        ? route('documentation.index', ['locale' => app()->getLocale()]).'#'.ltrim($docAnchor, '#')
                                        : null;
                                @endphp
                                @if($docUrl)
                                    <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2 rounded-full border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-700 hover:border-blue-300">
                                        {{ __('docs.view_link') }} ↗
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</div>
