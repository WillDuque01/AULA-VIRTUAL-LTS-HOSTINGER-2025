@php
    $categories = $integrationGuides ?? [];
    $wizardGuide = $wizardGuide ?? [];
@endphp

<div class="space-y-10">
    <div>
        <h2 class="text-xl font-semibold text-white">{{ __('Conecta tus integraciones esenciales') }}</h2>
        <p class="text-sm text-slate-400">
            {{ __('Puedes dejar los campos vacíos para trabajar en modo gratuito y completarlos después desde el panel de administración. Cada bloque incluye las instrucciones resumidas para conseguir los tokens correctos.') }}
        </p>
    </div>

    @if(!empty($wizardGuide['cards'] ?? []))
        <x-help.contextual-panel
            :guides="$wizardGuide['cards']"
            :title="$wizardGuide['title'] ?? __('Checklist')"
            :subtitle="$wizardGuide['subtitle'] ?? null" />
    @endif

    @foreach($categories as $category)
        @php
            $providers = $category['providers'] ?? [];
        @endphp
        <section class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-6">
            <div>
                <p class="text-xs uppercase font-semibold tracking-[0.35em] text-slate-500">{{ data_get($category, 'title') }}</p>
                <p class="text-sm text-slate-400">{{ data_get($category, 'description') }}</p>
            </div>

            @foreach($providers as $provider)
                @php
                    $fields = $provider['fields'] ?? [];
                @endphp
                <article class="rounded-2xl border border-slate-800/70 bg-slate-950/40 p-5 space-y-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">{{ $provider['name'] }}</p>
                            <p class="text-sm text-slate-300">{{ $provider['summary'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-slate-400">
                            @foreach($provider['tokens'] ?? [] as $token)
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-800/80 bg-slate-900/40 px-3 py-1">
                                    {{ $token['label'] ?? '' }}
                                    @if(!empty($token['hint']))
                                        <span class="text-slate-500 font-normal">{{ $token['hint'] }}</span>
                                    @endif
                                </span>
                            @endforeach
                            @if(!empty($provider['docs']))
                                <a href="{{ $provider['docs'] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 rounded-full border border-blue-500/30 px-3 py-1 text-blue-300 text-[11px] font-semibold hover:border-blue-400">
                                    {{ __('Ver docs') }} ↗
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(!empty($fields))
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach($fields as $field)
                                @php
                                    $binding = $field['binding'] ?? null;
                                @endphp
                                @if(! $binding)
                                    @continue
                                @endif
                                @php
                                    $type = $field['type'] ?? 'text';
                                @endphp
                                @if($type === 'toggle')
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-800/80 bg-slate-950/40 px-3 py-2 text-xs uppercase font-semibold text-slate-300">
                                        <input type="checkbox" wire:model.defer="{{ $binding }}"
                                               class="rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500" />
                                        <span>
                                            {{ $field['label'] ?? $binding }}
                                            @if(!empty($field['hint']))
                                                <span class="block text-[11px] font-normal normal-case text-slate-500">{{ $field['hint'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @else
                                    <label class="text-xs uppercase text-slate-400">
                                        {{ $field['label'] ?? $binding }}
                                        <input
                                            type="{{ $type }}"
                                            wire:model.defer="{{ $binding }}"
                                            @if(!empty($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500" />
                                        @if(!empty($field['hint']))
                                            <span class="mt-1 block text-[11px] font-semibold text-slate-500 normal-case">
                                                {{ $field['hint'] }}
                                            </span>
                                        @endif
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($provider['steps']))
                        <div class="rounded-2xl border border-slate-800/60 bg-slate-950/30 px-4 py-3">
                            <p class="text-[11px] uppercase font-semibold text-slate-500">{{ __('Cómo obtener estas credenciales') }}</p>
                            <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-slate-300">
                                @foreach($provider['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @if(!empty($provider['validation']))
                        <div class="rounded-2xl border border-emerald-900/40 bg-emerald-950/30 px-4 py-3">
                            <p class="text-[11px] uppercase font-semibold text-emerald-400">{{ __('Validación rápida') }}</p>
                            <ul class="mt-2 space-y-2 text-sm text-emerald-100">
                                @foreach($provider['validation'] as $check)
                                    <li>
                                        <p class="font-semibold">{{ $check['label'] ?? __('Prueba') }}</p>
                                        @if(!empty($check['command']))
                                            <code class="mt-1 inline-flex w-full overflow-x-auto rounded-xl border border-emerald-800/60 bg-emerald-900/40 px-3 py-1 text-xs font-mono text-emerald-200">
                                                {{ $check['command'] }}
                                            </code>
                                        @endif
                                        @if(!empty($check['description']))
                                            <p class="text-xs text-emerald-200/80 mt-1">{{ $check['description'] }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($provider['troubleshooting']))
                        <div class="rounded-2xl border border-amber-900/50 bg-amber-950/20 px-4 py-3">
                            <p class="text-[11px] uppercase font-semibold text-amber-400">{{ __('Tips de diagnóstico') }}</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-amber-100">
                                @foreach($provider['troubleshooting'] as $tip)
                                    <li>{{ $tip }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @endforeach
</div>
