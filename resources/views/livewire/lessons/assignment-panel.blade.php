<div class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Tarea</p>
                <h3 class="text-xl font-semibold text-slate-900">{{ data_get($lesson->config, 'title', 'Actividad') }}</h3>
            </div>
            <div class="text-sm text-slate-500">
                Puntaje máximo: <span class="font-semibold text-slate-900">{{ $assignment->max_points }} pts</span>
                @if($assignment->due_at)
                    · Vence {{ $assignment->due_at->diffForHumans() }}
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
            @if($assignment->requires_approval)
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-amber-700 font-semibold">
                    ✅ Requiere aprobación docente
                </span>
                <span>
                    Necesitas al menos {{ $assignment->passing_score }}% ({{ ceil(($assignment->passing_score / 100) * $assignment->max_points) }} pts) para desbloquear la siguiente lección.
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 font-semibold">
                    ✨ Se desbloquea al enviar
                </span>
            @endif
        </div>
        @if($assignment->instructions)
            <div class="prose prose-slate max-w-none text-sm text-slate-700">
                {!! \Illuminate\Support\Str::markdown($assignment->instructions) !!}
            </div>
        @else
            <p class="text-sm text-slate-500">El profesor aún no ha añadido instrucciones.</p>
        @endif
        @if(!empty($assignment->rubric))
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-xs uppercase text-slate-500 font-semibold">Criterios de evaluación</p>
                <ul class="mt-2 text-sm text-slate-700 space-y-1 list-disc list-inside">
                    @foreach($assignment->rubric as $criterion)
                        <li>{{ $criterion }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($link = \App\Support\Integrations\WhatsAppLink::assignment([
            'title' => data_get($lesson->config, 'title', 'Tarea'),
            'status' => $submission?->status ?? 'pending',
        ]))
            <div>
                <a href="{{ $link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-300">
                    {{ __('whatsapp.assignment.help_cta') }} ↗
                </a>
            </div>
        @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        @if($submission && $submission->status === 'graded')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Calificada: {{ $submission->score }} / {{ $submission->max_points }}
                @if($submission->feedback)
                    <p class="mt-1 text-emerald-700">{{ $submission->feedback }}</p>
                @endif
            </div>
        @elseif($submission && $submission->status === 'rejected')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                Tu entrega fue rechazada. Revisa el feedback y vuelve a enviar.
                @if($submission->feedback)
                    <p class="mt-1 text-rose-700">{{ $submission->feedback }}</p>
                @endif
            </div>
        @elseif($submission && $submission->status === 'submitted')
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Recibimos tu entrega. Quedará desbloqueada cuando un profesor la apruebe.
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-4">
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Respuesta</label>
                <textarea wire:model.defer="body" rows="5" class="mt-1 block w-full rounded-2xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Describe tu entrega, incluye enlaces o detalles necesarios..."></textarea>
                @error('body')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs uppercase font-semibold text-slate-500">Enlace (opcional)</label>
                <input type="url" wire:model.defer="attachmentUrl" class="mt-1 block w-full rounded-2xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://drive.google.com/...">
                @error('attachmentUrl')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Puedes reenviar antes de la calificación. Las entregas se sellan con fecha y hora.
                </p>
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    Entregar tarea
                </button>
            </div>
        </form>
    </div>
</div>


