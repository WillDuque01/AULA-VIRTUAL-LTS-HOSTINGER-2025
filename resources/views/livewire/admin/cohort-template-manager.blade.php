<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Planner Discord') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Plantillas de cohorte') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Centraliza presets reutilizables para los docentes (horarios, duración, cupos y pack asociado).') }}</p>
            </div>
            <button type="button"
                    wire:click="resetForm"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400">
                {{ __('Nueva plantilla') }}
            </button>
        </div>
    </header>

    <section class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <div class="space-y-4">
            @forelse($templates as $template)
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $template->name }}</p>
                            <p class="text-xs text-slate-500">{{ $template->description }}</p>
                        </div>
                        <div class="flex gap-2 text-xs font-semibold">
                            <button type="button"
                                    wire:click="edit({{ $template->id }})"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-indigo-200 hover:text-indigo-700">
                                {{ __('Editar') }}
                            </button>
                            <button type="button"
                                    wire:click="delete({{ $template->id }})"
                                    class="rounded-full border border-rose-200 px-3 py-1 text-rose-600 hover:border-rose-300">
                                {{ __('Eliminar') }}
                            </button>
                        </div>
                    </div>
                    @php
                        $availableSlots = $template->remainingSlots();
                    @endphp
                    <div class="flex flex-wrap gap-2 text-[11px] text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">⌛ {{ $template->duration_minutes }} min</span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">👥 {{ $template->capacity }} {{ __('cupos') }}</span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                            ✅ {{ __('Inscritos: :count', ['count' => $template->enrolled_count ?? 0]) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 {{ $availableSlots > 0 ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 'border-rose-200 text-rose-600 bg-rose-50' }}">
                            @if($availableSlots > 0)
                                🔓 {{ __('Cupos disponibles: :count', ['count' => $availableSlots]) }}
                            @else
                                ⛔ {{ __('Agotado') }}
                            @endif
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 capitalize">{{ $template->type }}</span>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                            ${{ number_format($template->price_amount, 2) }} {{ $template->price_currency }}
                        </span>
                        @if($template->cohort_label)
                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">{{ $template->cohort_label }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                            {{ __('Estado') }}: {{ __($template->status) }}
                        </span>
                        @if($template->requires_package)
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-emerald-700">
                                🎟 {{ __('Requiere pack') }}
                            </span>
                        @endif
                        @if($template->is_featured)
                            <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-indigo-700">
                                ⭐ {{ __('Destacado') }}
                            </span>
                        @endif
                        @php
                            $productMeta = $connectedProducts[$template->id] ?? null;
                        @endphp
                        @if($productMeta && !empty($productMeta['product_id']))
                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 text-slate-600">
                                🛒 {{ __('Producto') }} #{{ $productMeta['product_id'] }}
                            </span>
                            @if(strtolower($productMeta['status'] ?? '') !== strtolower($template->status))
                                <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-amber-700">
                                    ⚠ {{ __('Desincronizado') }}
                                </span>
                            @endif
                            @if(! is_null($productMeta['inventory']))
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5">
                                    🧮 {{ __('Inventario producto: :count', ['count' => max(0, $productMeta['inventory'])]) }}
                                </span>
                            @endif
                        @endif
                    </div>
                    @php
                        $slotSummary = collect($template->slots ?? [])
                            ->map(function ($slot) use ($weekdayOptions) {
                                $weekdayKey = strtolower((string) ($slot['weekday'] ?? ''));
                                $weekdayLabel = $weekdayKey
                                    ? ucfirst($weekdayOptions[$weekdayKey] ?? $weekdayKey)
                                    : __('Sin día');
                                $timeLabel = $slot['time'] ?? '--:--';

                                if (! $weekdayKey && $timeLabel === '--:--') {
                                    return null;
                                }

                                return "{$weekdayLabel} · {$timeLabel}";
                            })
                            ->filter()
                            ->implode(' | ');
                    @endphp
                    @if(! empty($slotSummary))
                        <p class="text-xs text-slate-500">{{ $slotSummary }}</p>
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-500">{{ __('Aún no hay plantillas guardadas.') }}</p>
            @endforelse
        </div>

        <form wire:submit.prevent="save" class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ $form['id'] ? __('Editar plantilla') : __('Nueva plantilla') }}</p>
                <h2 class="text-lg font-semibold text-slate-900">{{ $form['name'] ?: __('Sin título') }}</h2>
            </div>
            <label class="block text-xs font-semibold text-slate-600">
                {{ __('Nombre visible') }}
                <input type="text" wire:model.defer="form.name" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('form.name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </label>
            <label class="block text-xs font-semibold text-slate-600">
                {{ __('Slug (opcional)') }}
                <input type="text" wire:model.defer="form.slug" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </label>
            <label class="block text-xs font-semibold text-slate-600">
                {{ __('Descripción') }}
                <textarea wire:model.defer="form.description" rows="2" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </label>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Tipo') }}
                    <select wire:model.defer="form.type" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="cohort">{{ __('Cohorte') }}</option>
                        <option value="global">{{ __('Global') }}</option>
                    </select>
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Etiqueta cohort') }}
                    <input type="text" wire:model.defer="form.cohort_label" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Duración (min)') }}
                    <input type="number" wire:model.defer="form.duration_minutes" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Cupos') }}
                    <input type="number" wire:model.defer="form.capacity" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Precio') }}
                    <input type="number" step="0.01" min="0" wire:model.defer="form.price_amount" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Moneda') }}
                    <input type="text" wire:model.defer="form.price_currency" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm uppercase">
                </label>
                <label class="block text-xs font-semibold text-slate-600">
                    {{ __('Estado') }}
                    <select wire:model.defer="form.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="draft">{{ __('Borrador') }}</option>
                        <option value="published">{{ __('Publicado') }}</option>
                        <option value="archived">{{ __('Archivado') }}</option>
                    </select>
                </label>
            </div>
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                <input type="checkbox" wire:model="form.is_featured" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                {{ __('Destacar en el catálogo') }}
            </label>
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                <input type="checkbox" wire:model="form.requires_package" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                {{ __('Requiere pack') }}
            </label>
            <label class="block text-xs font-semibold text-slate-600">
                {{ __('Pack (opcional)') }}
                <select wire:model.defer="form.practice_package_id" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm" @disabled(!$form['requires_package'])>
                    <option value="">{{ __('Sin pack') }}</option>
                    @foreach($packages as $pack)
                        <option value="{{ $pack->id }}">{{ $pack->title }} · {{ $pack->sessions_count }} {{ __('sesiones') }}</option>
                    @endforeach
                </select>
            </label>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Bloques') }}</p>
                    <button type="button"
                            wire:click="addSlot"
                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">
                        ➕ {{ __('Añadir') }}
                    </button>
                </div>
                @foreach($form['slots'] as $index => $slot)
                    <div class="grid gap-2 sm:grid-cols-[1fr,1fr,auto] items-center">
                        <select wire:model="form.slots.{{ $index }}.weekday" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                            @foreach($weekdayOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="time" wire:model="form.slots.{{ $index }}.time" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                        <button type="button"
                                wire:click="removeSlot({{ $index }})"
                                class="rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-500 hover:border-rose-200 hover:text-rose-600">
                            ✕
                        </button>
                    </div>
                @endforeach
                @error('form.slots') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        wire:click="resetForm"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-500 hover:border-slate-400">
                    {{ __('Cancelar') }}
                </button>
                <button type="submit"
                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Guardar') }}
                </button>
            </div>
        </form>
    </section>
</div>

