<div class="space-y-6">
    @if(! $quiz)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-6">
            <p class="text-sm font-semibold">{{ __('Quiz content has not been configured yet.') }}</p>
            <p class="text-xs text-amber-700 mt-1">{{ __('Let your teacher know to enable the questions.') }}</p>
        </div>
        @return
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Evaluation') }}</p>
                <h3 class="text-xl font-semibold text-slate-900">{{ data_get($lesson->config, 'title', 'Quiz interactivo') }}</h3>
            </div>
            @if($lastAttempt)
                <div class="text-sm text-slate-500">
                    {{ __('Last attempt:') }} <span class="font-semibold text-slate-900">{{ optional($lastAttempt->created_at)->diffForHumans() }}</span>
                    · <span class="text-emerald-600 font-semibold">{{ $lastAttempt->score }}/{{ $lastAttempt->max_score }}</span>
                </div>
            @endif
        </div>
        <p class="text-sm text-slate-500">{{ __('Answer all questions. The system grades automatically and records your attempt.') }}</p>
    </div>

    <form wire:submit.prevent="submit" class="space-y-6">
        @foreach($questions as $question)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <div class="flex items-start gap-3">
                    <span class="text-sm font-semibold text-slate-400">Q{{ $loop->iteration }}</span>
                    <div>
                        <p class="text-base font-semibold text-slate-900">{{ $question['prompt'] }}</p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">{{ $question['type'] === 'mcq' ? 'Opción múltiple' : 'Verdadero/Falso' }}</p>
                    </div>
                </div>

                @if($question['type'] === 'mcq')
                    <div class="space-y-2">
                        @foreach($question['options'] as $option)
                            <label class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm {{ ($answers[$question['id']] ?? null) == $option['id'] ? 'border-blue-400 bg-blue-50' : 'border-slate-200' }}">
                                <input type="radio" wire:model="answers.{{ $question['id'] }}" value="{{ $option['id'] }}" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700">{{ $option['text'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-2 md:grid-cols-2">
                        <label class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm {{ ($answers[$question['id']] ?? null) === 'true' ? 'border-blue-400 bg-blue-50' : 'border-slate-200' }}">
                            <input type="radio" wire:model="answers.{{ $question['id'] }}" value="true" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-slate-700">Verdadero</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm {{ ($answers[$question['id']] ?? null) === 'false' ? 'border-blue-400 bg-blue-50' : 'border-slate-200' }}">
                            <input type="radio" wire:model="answers.{{ $question['id'] }}" value="false" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-slate-700">Falso</span>
                        </label>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">{{ __('Your submission is saved automatically and you can repeat it to improve your score.') }}</p>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700">
                Calificar intento
            </button>
        </div>
    </form>

    @if($submitted)
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 shadow-inner">
            <p class="text-xs uppercase tracking-wide text-emerald-600 font-semibold">Resultado</p>
            <h4 class="text-2xl font-bold text-emerald-800">{{ $results['score'] }}/{{ $results['max_score'] }} ({{ $results['percentage'] }}%)</h4>
            <p class="text-sm text-emerald-700">¡Excelente! Seguimos acompañándote en tu progreso.</p>
        </div>
    @endif
</div>


