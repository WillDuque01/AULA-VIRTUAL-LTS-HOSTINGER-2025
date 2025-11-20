<div class="space-y-6">
    <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-inner">
        <div class="flex items-center gap-3 text-amber-900">
            <span class="text-2xl">🔒</span>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide">Lección bloqueada</p>
                <p class="text-base font-semibold">{{ $lockReason }}</p>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 text-sm text-amber-800/90">
            @if($releaseAtHuman)
                <div class="flex items-center gap-2">
                    <span class="text-xl">⏳</span>
                    Disponible en {{ $releaseAtHuman }}
                </div>
            @endif
            @if($prerequisiteLesson)
                <div class="flex items-center gap-2">
                    <span class="text-xl">✅</span>
                    Completa "{{ data_get($prerequisiteLesson->config, 'title', $prerequisiteLesson->chapter?->title) }}"
                </div>
            @endif
        </div>
        @if($prerequisiteLesson)
            <div class="mt-4">
                <a href="{{ route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $prerequisiteLesson]) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700">
                    Ir a la lección previa
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        @endif
    </div>
</div>
<div class="space-y-6">
    <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-inner">
        <div class="flex items-center gap-3 text-amber-900">
            <span class="text-2xl">🔒</span>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide">Lección bloqueada</p>
                <p class="text-base font-semibold">{{ $lockReason }}</p>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 text-sm text-amber-800/90">
            @if($releaseAtHuman)
                <div class="flex items-center gap-2">
                    <span class="text-xl">⏳</span>
                    Disponible en {{ $releaseAtHuman }}
                </div>
            @endif
            @if($prerequisiteLesson)
                <div class="flex items-center gap-2">
                    <span class="text-xl">✅</span>
                    Completa "{{ data_get($prerequisiteLesson->config, 'title', $prerequisiteLesson->chapter?->title) }}"
                </div>
            @endif
        </div>
        @if($prerequisiteLesson)
            <div class="mt-4">
                <a href="{{ route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $prerequisiteLesson]) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700">
                    Ir a la lección previa
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        @endif
    </div>
</div>

