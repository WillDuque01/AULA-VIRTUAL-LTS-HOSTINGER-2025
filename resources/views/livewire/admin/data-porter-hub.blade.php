<div
    x-data
    x-on:data-porter:download.window="window.open($event.detail.url, '_blank')"
    class="space-y-6"
>
    <div class="rounded-3xl border border-slate-100 bg-white/80 p-6 shadow-lg shadow-slate-200/50 space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">{{ __('Centro de datos unificado') }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ $currentDataset['label'] ?? __('Selecciona un dataset') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $currentDataset['description'] ?? __('Descarga o importa datos para Analytics, GA4 o reportes internos.') }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <p class="font-semibold text-slate-900">{{ __('Formatos disponibles') }}</p>
                <p class="text-xs">{{ __('CSV (para Sheets/Excel) y JSON (servicios externos)') }}</p>
                @if($isTeacherRestricted)
                    <p class="mt-2 text-[11px] font-semibold uppercase tracking-wide text-amber-600">
                        {{ __('Teacher Admin: filtra por curso o lección antes de exportar.') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Eventos pendientes') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $telemetryStatus['pending'] ?? 0 }}</p>
                <p class="text-xs text-slate-500">
                    {{ __('Última sync:') }}
                    {{ $telemetryStatus['last_synced_at'] ? $telemetryStatus['last_synced_at']->diffForHumans() : __('Nunca') }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Drivers activos') }}</p>
                <ul class="mt-2 space-y-2 text-sm text-slate-700">
                    @foreach($telemetryStatus['drivers'] ?? [] as $driver)
                        <li class="flex items-center justify-between rounded-xl border px-3 py-2 {{ $driver['enabled'] ? 'border-emerald-100 bg-emerald-50/60' : 'border-slate-100 bg-white' }}">
                            <span class="font-semibold">{{ $driver['label'] }}</span>
                            <span class="text-xs {{ $driver['enabled'] ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $driver['enabled'] ? __('Activo') : __('Inactivo') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-500">{{ __('Sincronización manual') }}</p>
                <p class="mt-1 text-xs text-slate-600">{{ __('Ejecuta `telemetry:sync` para empujar el lote actual a GA4/Mixpanel.') }}</p>
                <button type="button"
                        wire:click="syncTelemetry"
                        wire:loading.attr="disabled"
                        class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove>{{ __('Sincronizar ahora') }}</span>
                    <span wire:loading>{{ __('Sincronizando...') }}</span>
                </button>
                @if($lastSyncMessage)
                    <p class="mt-2 text-[11px] font-semibold text-indigo-700">{{ $lastSyncMessage }}</p>
                @endif
                @error('telemetry')
                    <p class="mt-2 text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if(!empty($telemetryStatus['logs']) && $telemetryStatus['logs']->count() > 0)
            <div class="rounded-2xl border border-slate-100 bg-white/60 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Historial de sincronizaciones') }}</p>
                    <span class="text-[11px] text-slate-400">{{ __('Últimos :count registros', ['count' => $telemetryStatus['logs']->count()]) }}</span>
                </div>
                <div class="mt-3 space-y-3">
                    @foreach($telemetryStatus['logs'] as $log)
                        @php
                            $badgeClasses = match($log->status) {
                                'success' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
                                'failed' => 'border-rose-100 bg-rose-50 text-rose-700',
                                default => 'border-amber-100 bg-amber-50 text-amber-700',
                            };
                        @endphp
                        <div class="flex flex-col gap-2 rounded-2xl border border-slate-100 bg-white/70 p-4 shadow-sm shadow-slate-100/60 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ optional($log->created_at)->timezone(config('app.timezone'))->format('d M, H:i') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $log->message ?? __('Sin detalles adicionales') }}
                                </p>
                                @if($log->user)
                                    <p class="text-xs text-slate-400">
                                        {{ __('Ejecutado por :name', ['name' => $log->user->name]) }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-start gap-1 text-right lg:items-end">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $badgeClasses }}">
                                    {{ __($log->status === 'success' ? 'OK' : ($log->status === 'failed' ? 'Error' : 'Saltado')) }}
                                </span>
                                <p class="text-xs font-semibold text-slate-700">
                                    {{ trans_choice('{0}Sin eventos|{1}1 evento|[2,*]:count eventos', $log->processed, ['count' => $log->processed]) }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    {{ __('Drivers: :count · :seconds s', ['count' => $log->driver_count, 'seconds' => $log->duration_ms ? number_format($log->duration_ms / 1000, 2) : '0']) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-indigo-100 bg-white/90 p-6 shadow-xl shadow-indigo-100/80">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-indigo-500">{{ __('Exportar datos') }}</p>
                    <h2 class="text-xl font-semibold text-slate-900">{{ __('Descarga inmediata') }}</h2>
                </div>
                <span class="text-2xl" aria-hidden="true">📤</span>
            </div>

            <div class="mt-4 space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Dataset') }}
                    <select wire:model="dataset"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($datasets as $key => $info)
                            <option value="{{ $key }}">{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Formato') }}
                    <select wire:model="format"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </label>

                @if(! empty($filtersSchema))
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Filtros opcionales') }}</p>
                        <div class="mt-3 grid gap-3">
                            @foreach($filtersSchema as $key => $filter)
                                @php
                                    $inputType = match($filter['type'] ?? 'text') {
                                        'date' => 'date',
                                        'number' => 'number',
                                        default => 'text',
                                    };
                                @endphp
                                <label class="block text-xs font-semibold text-slate-600">
                                    {{ $filter['label'] ?? Str::headline($key) }}
                                    <input type="{{ $inputType }}"
                                           wire:model.lazy="filters.{{ $key }}"
                                           class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </label>
                            @endforeach
                        </div>
                        @error('filters')
                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <button type="button"
                    wire:click="download"
                    wire:loading.attr="disabled"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove>{{ __('Descargar dataset') }}</span>
                <span wire:loading>{{ __('Preparando...') }}</span>
            </button>
        </section>

        <section class="rounded-3xl border border-emerald-100 bg-white/90 p-6 shadow-xl shadow-emerald-100/80">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-emerald-500">{{ __('Importar datos') }}</p>
                    <h2 class="text-xl font-semibold text-slate-900">{{ __('Sincronizar snapshots') }}</h2>
                </div>
                <span class="text-2xl" aria-hidden="true">📥</span>
            </div>

            @if(empty($importDatasets))
                <p class="mt-4 text-sm text-slate-600">
                    {{ __('Por ahora no hay datasets habilitados para importación con tu perfil.') }}
                </p>
            @else
                <div class="mt-4 space-y-4">
                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Dataset importable') }}
                        <select wire:model="importDataset"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($importDatasets as $key => $info)
                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Archivo (.csv o .json)') }}
                        <input type="file"
                               wire:model="importFile"
                               class="mt-1 w-full rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/40 px-3 py-2 text-sm text-emerald-700 focus:border-emerald-500 focus:ring-emerald-500" />
                    </label>
                    @error('importFile')
                        <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="button"
                        wire:click="import"
                        wire:loading.attr="disabled"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove>{{ __('Importar archivo') }}</span>
                    <span wire:loading>{{ __('Procesando...') }}</span>
                </button>
            @endif

            @if($lastImportSummary)
                <p class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-xs font-semibold text-emerald-700">
                    {{ $lastImportSummary }}
                </p>
            @endif
        </section>
    </div>
</div>

