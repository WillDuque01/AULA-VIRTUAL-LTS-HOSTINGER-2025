<div class="space-y-6">
    <div class="player-slide-up bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-100 p-6 space-y-4">
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
            @if($badge)
                <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-3 py-0.5 text-xs font-semibold text-blue-600">{{ $badge }}</span>
            @endif
        </div>
        <p class="text-sm text-gray-500 capitalize">Tipo de contenido: {{ $lesson->type }}</p>
        @if($lesson->type === 'text' && $bodyContent)
            <div class="player-fade-in prose prose-slate max-w-none">
                {!! \Illuminate\Support\Str::markdown($bodyContent) !!}
            </div>
        @else
            @if($resourceUrl)
                <a href="{{ $resourceUrl }}" target="_blank" rel="noopener"
                   x-on:click="window.playerSignals?.emit('resource_click', { metadata: { type: 'external', lesson_id: {{ $lesson->id }} } })"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-full shadow-sm hover:bg-blue-700">
                    Abrir recurso externo
                </a>
            @else
                <p class="text-sm text-gray-500">Configura el recurso de esta lección desde el builder.</p>
            @endif
        @endif
        @if($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}" target="_blank" rel="noopener"
               x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'resource', origin: 'static', label: @js($ctaLabel) } })"
               class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                {{ $ctaLabel }} ↗
            </a>
        @endif
    </div>
</div>

