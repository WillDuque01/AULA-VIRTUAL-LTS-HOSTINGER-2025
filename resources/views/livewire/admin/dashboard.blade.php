<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Usuarios</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['users'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Suscripciones activas</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['active_subscriptions'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Ingresos 30d</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">${{ number_format($metrics['mrr'], 2) }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Horas vistas</p>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metrics['watch_hours'] }}</p>
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

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-[0.3em]">{{ __('outbox.title') }}</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('outbox.subtitle') }}</h3>
                <p class="text-xs text-slate-500">
                    {{ __('Pendientes: :pending · Fallidos: :failed', ['pending' => $outboxStats['pending'], 'failed' => $outboxStats['failed']]) }}
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
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Ingresos</p>
                <h4 class="text-lg font-semibold text-slate-900">Últimos 14 días</h4>
            </div>
            <div class="p-6">
                @if($revenueTrend->isEmpty())
                    <p class="text-sm text-slate-500">Sin eventos de pago.</p>
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
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Video</p>
                <h4 class="text-lg font-semibold text-slate-900">Horas vistas por curso</h4>
            </div>
            <div class="p-6">
                @if($watchPerCourse->isEmpty())
                    <p class="text-sm text-slate-500">Aún no hay métricas.</p>
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
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Integraciones</p>
            <h4 class="text-lg font-semibold text-slate-900">Estado actual</h4>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($integrationStatus as $key => $status)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $status['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $status['status'] }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $status['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $status['ok'] ? 'OK' : 'Pendiente' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
