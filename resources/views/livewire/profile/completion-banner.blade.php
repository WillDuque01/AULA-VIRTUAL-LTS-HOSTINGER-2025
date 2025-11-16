@php use Illuminate\Support\Str; @endphp
<div>
@if(($summary['percent'] ?? 100) < 100 && $summary['steps'] ?? false)
    <section class="bg-amber-50/80 border border-amber-100 py-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-amber-500">{{ __('Completa tu perfil') }}</p>
                    <h3 class="text-lg font-semibold text-amber-900">
                        {{ __('Tu perfil está completado al :percent%', ['percent' => $summary['percent']]) }}
                    </h3>
                    <p class="text-sm text-amber-800/80">
                        {{ __('Añade tus datos para personalizar recomendaciones, certificados y recordatorios.') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-32 h-2 rounded-full bg-amber-100">
                        <div class="h-full rounded-full bg-amber-500" style="width: {{ $summary['percent'] }}%"></div>
                    </div>
                    <button type="button"
                            wire:click="dismiss"
                            class="text-xs font-semibold uppercase tracking-wide text-amber-600 hover:text-amber-700">
                        {{ __('Recordármelo después') }}
                    </button>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach($summary['steps'] as $step)
                    <div class="rounded-2xl border px-4 py-3 {{ $step['completed'] ? 'border-emerald-100 bg-white' : 'border-amber-100 bg-white/90' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $step['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $step['description'] }}</p>
                            </div>
                            <span class="text-lg">
                                {{ $step['completed'] ? '✅' : '📝' }}
                            </span>
                        </div>
                        @if(!$step['completed'] && $expanded === $step['key'])
                            <div class="mt-4 space-y-3">
                                @if($step['key'] === 'teacher')
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600" for="banner-headline">
                                            {{ __('Título profesional') }}
                                        </label>
                                        <input type="text"
                                               id="banner-headline"
                                               wire:model.defer="form.headline"
                                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        @error('form.headline')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600" for="banner-bio">
                                            {{ __('Resumen / Bio') }}
                                        </label>
                                        <textarea id="banner-bio"
                                                  wire:model.defer="form.bio"
                                                  rows="3"
                                                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                                        @error('form.bio')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-teaching-since">
                                                {{ __('Enseñando desde') }}
                                            </label>
                                            <input type="text"
                                                   id="banner-teaching-since"
                                                   wire:model.defer="form.teaching_since"
                                                   placeholder="2018"
                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                            @error('form.teaching_since')
                                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-linkedin">
                                                {{ __('LinkedIn o portafolio') }}
                                            </label>
                                            <input type="url"
                                                   id="banner-linkedin"
                                                   wire:model.defer="form.linkedin_url"
                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                            @error('form.linkedin_url')
                                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-specialties">
                                                {{ __('Especialidades (coma)') }}
                                            </label>
                                            <input type="text"
                                                   id="banner-specialties"
                                                   wire:model.defer="form.specialties_input"
                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-languages">
                                                {{ __('Idiomas (coma)') }}
                                            </label>
                        <input type="text"
                               id="banner-languages"
                               wire:model.defer="form.languages_input"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-certifications">
                                                {{ __('Certificaciones (coma)') }}
                                            </label>
                                            <input type="text"
                                                   id="banner-certifications"
                                                   wire:model.defer="form.certifications_input"
                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600" for="banner-notes">
                                            {{ __('Notas internas') }}
                                        </label>
                                        <textarea id="banner-notes"
                                                  wire:model.defer="form.teacher_notes"
                                                  rows="2"
                                                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                                        @error('form.teacher_notes')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @else
                                    @foreach($step['fields'] as $field)
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600" for="banner-{{ $field }}">
                                                {{ Str::of($field)->replace('_', ' ')->title() }}
                                            </label>
                                            <input type="text"
                                                   id="banner-{{ $field }}"
                                                   wire:model.defer="form.{{ $field }}"
                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500" />
                                            @error('form.'.$field)
                                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                @endif
                                <button type="button"
                                        wire:click="saveSection('{{ $step['key'] }}')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex w-full items-center justify-center rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-amber-700 disabled:opacity-60">
                                    <span wire:loading.remove>{{ __('Guardar sección') }}</span>
                                    <span wire:loading>{{ __('Guardando...') }}</span>
                                </button>
                            </div>
                        @else
                            @if(!$step['completed'])
                                <button type="button"
                                        wire:click="$set('expanded', '{{ $step['key'] }}')"
                                        class="mt-3 text-xs font-semibold text-amber-600 hover:text-amber-700">
                                    {{ __('Completar ahora') }}
                                </button>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
</div>

