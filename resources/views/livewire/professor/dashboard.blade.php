<div class="space-y-6">
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
</div>
