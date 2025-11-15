<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Tareas</p>
            <h2 class="text-2xl font-semibold text-slate-900">Gestión de entregas</h2>
        </div>
        <div>
            <select wire:model="selectedAssignmentId" class="rounded-full border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment['id'] }}">{{ $assignment['title'] }} {{ $assignment['course'] ? '· '.$assignment['course'] : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Submissions</p>
                <h4 class="text-lg font-semibold text-slate-900">{{ count($submissions) }} entregas</h4>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($submissions as $submission)
                <div class="px-6 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $submission['student'] }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($submission['status']) }} · {{ $submission['submitted_at'] ?? 'Sin enviar' }}</p>
                        <p class="text-sm text-slate-700 mt-2 line-clamp-2">{{ $submission['body'] }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            @if($submission['attachment_url'])
                                <a href="{{ $submission['attachment_url'] }}" target="_blank" class="text-xs text-blue-600">Ver adjunto ↗</a>
                            @endif
                            @php($followUpLink = \App\Support\Integrations\WhatsAppLink::assignment(
                                [
                                    'title' => $submission['assignment_title'] ?? 'Tarea',
                                    'status' => $submission['status'] ?? 'pending',
                                ],
                                'admin.assignments-manager',
                                ['submission_id' => $submission['id']]
                            ))
                            @if($followUpLink)
                                <a href="{{ $followUpLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-400">
                                    {{ __('whatsapp.assignment.followup_cta') }} ↗
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ $submission['score'] ? $submission['score'].' pts' : 'Sin puntuar' }}</span>
                        <button wire:click="editSubmission({{ $submission['id'] }})" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-700">Evaluar</button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-slate-500">
                    No hay entregas registradas para esta tarea.
                </div>
            @endforelse
        </div>
    </div>

    @if($editingSubmissionId)
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
            <h3 class="text-lg font-semibold text-slate-900">Calificar entrega</h3>
            <form wire:submit.prevent="saveGrade" class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs uppercase font-semibold text-slate-500">Puntaje</label>
                    <input type="number" min="0" wire:model.defer="score" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('score')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase font-semibold text-slate-500">Feedback</label>
                    <textarea wire:model.defer="feedback" rows="3" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Comentarios personalizados, observaciones de la rúbrica..."></textarea>
                    @error('feedback')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase font-semibold text-slate-500">Rechazar entrega</label>
                    <select wire:model.defer="selectedRejectionReason" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-rose-500 focus:ring-rose-500">
                        <option value="">{{ __('dashboard.assignments.reject_placeholder') }}</option>
                        @foreach($rejectionReasons as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Selecciona un motivo y usa el feedback para detallar.</p>
                </div>
                <div class="md:col-span-2 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('editingSubmissionId', null)" class="text-sm text-slate-500">Cancelar</button>
                    <button type="button" wire:click="rejectSubmission({{ $editingSubmissionId }})" class="inline-flex items-center rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-rose-700">Rechazar entrega</button>
                    <button type="submit" class="inline-flex items-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">Guardar calificación</button>
                </div>
            </form>
        </div>
    @endif
</div>


