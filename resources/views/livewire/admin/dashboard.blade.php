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
