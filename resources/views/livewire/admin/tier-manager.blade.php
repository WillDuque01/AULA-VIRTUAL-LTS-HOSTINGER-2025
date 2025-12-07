<div class="mx-auto max-w-7xl space-y-10 px-4 py-12">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400">Teacher Admin</p>
            <h1 class="text-3xl font-semibold text-slate-100">{{ __('Gestion de tiers y grupos') }}</h1>
            <p class="text-sm text-slate-400">{{ __('Define quien accede a cursos premium, configura precios y agrupa cohortes desde un solo panel.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="createTier" class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400">{{ __('Nuevo tier') }}</button>
        </div>
    </div>

    @if (session()->has('status'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[1.6fr,1fr]">
        <section class="space-y-4">
            @forelse ($tiers as $tier)
                @php
                    $color = data_get($tier->metadata, 'color', '#1d4ed8');
                @endphp
                <article class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-slate-100">{{ $tier->name }}</span>
                                <span class="text-xs uppercase tracking-wide text-slate-500">/{{ $tier->slug }}</span>
                                @if($tier->is_default)
                                    <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">{{ __('Predeterminado') }}</span>
                                @endif
                                @unless($tier->is_active)
                                    <span class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-semibold text-amber-300">{{ __('Inactivo') }}</span>
                                @endunless
                            </div>
                            @if($tier->tagline)
                                <p class="text-sm text-slate-400">{{ $tier->tagline }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end text-right text-sm text-slate-400">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Tipo') }}</span>
                            <span class="text-base font-semibold text-slate-100">{{ \Illuminate\Support\Str::title($tier->access_type) }}</span>
                            @if($tier->access_type === 'free')
                                <span class="text-emerald-300 text-sm font-semibold">{{ __('Gratis') }}</span>
                            @else
                                <span class="text-slate-100 text-sm font-semibold">{{ $tier->currency }} {{ number_format($tier->price_monthly ?? 0, 2) }} <span class="text-slate-500">/ {{ __('mes') }}</span></span>
                                @if($tier->price_yearly)
                                    <span class="text-xs text-slate-500">{{ __('o') }} {{ $tier->currency }} {{ number_format($tier->price_yearly, 2) }} / {{ __('año') }}</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Usuarios activos') }}</p>
                            <p class="mt-1 text-lg font-semibold text-slate-100">{{ $tier->active_users_count ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Grupos asociados') }}</p>
                            <p class="mt-1 text-lg font-semibold text-slate-100">{{ $tier->groups_count ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Prioridad') }}</p>
                            <p class="mt-1 text-lg font-semibold text-slate-100">{{ $tier->priority }}</p>
                        </div>
                    </div>

                    @if(!empty($tier->features))
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($tier->features as $feature)
                                <span class="rounded-full border border-slate-800 bg-slate-800/60 px-3 py-1 text-xs text-slate-200">{{ $feature }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($tier->description)
                        <p class="mt-4 text-sm text-slate-400">{{ $tier->description }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" wire:click="editTier({{ $tier->id }})" class="rounded-full bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-400">{{ __('Editar') }}</button>
                        <button type="button" wire:click="toggleActive({{ $tier->id }})" class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-slate-500">
                            {{ $tier->is_active ? __('Desactivar') : __('Activar') }}
                        </button>
                        <button type="button" wire:click="setDefault({{ $tier->id }})" class="rounded-full border border-emerald-500 px-4 py-2 text-sm font-semibold text-emerald-300 hover:bg-emerald-500/10">{{ __('Marcar como predeterminado') }}</button>
                        <button type="button" wire:click="deleteTier({{ $tier->id }})" class="rounded-full border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10">{{ __('Eliminar') }}</button>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center text-slate-400">
                    {{ __('Aun no hay tiers configurados. Crea el primero para comenzar.') }}
                </div>
            @endforelse
        </section>

        <form wire:submit.prevent="saveTier" class="flex h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur">
            <h2 class="text-lg font-semibold text-slate-100">{{ $editingId ? __('Editar tier') : __('Crear nuevo tier') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Completa la informacion para guardar cambios al instante.') }}</p>

            <div class="mt-6 space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-name">{{ __('Nombre') }}</label>
                    <input id="tier-name" type="text" wire:model.defer="form.name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.name')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-slug">{{ __('Slug') }}</label>
                    <input id="tier-slug" type="text" wire:model.defer="form.slug" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="vip-access">
                    @error('form.slug')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-tagline">{{ __('Tagline') }}</label>
                    <input id="tier-tagline" type="text" wire:model.defer="form.tagline" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="{{ __('Descripcion corta para dashboards y cards') }}">
                    @error('form.tagline')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-description">{{ __('Descripcion') }}</label>
                    <textarea id="tier-description" rows="3" wire:model.defer="form.description" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="{{ __('Explica los beneficios principales del tier.') }}"></textarea>
                    @error('form.description')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-access">{{ __('Tipo de acceso') }}</label>
                        <select id="tier-access" wire:model.defer="form.access_type" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="free">{{ __('Gratis') }}</option>
                            <option value="paid">{{ __('Pago') }}</option>
                            <option value="vip">{{ __('VIP') }}</option>
                        </select>
                        @error('form.access_type')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-priority">{{ __('Prioridad') }}</label>
                        <input id="tier-priority" type="number" min="0" wire:model.defer="form.priority" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @error('form.priority')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-currency">{{ __('Moneda') }}</label>
                        <input id="tier-currency" type="text" maxlength="3" wire:model.defer="form.currency" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm uppercase text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @error('form.currency')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-price-month">{{ __('Precio mensual') }}</label>
                        <input id="tier-price-month" type="number" step="0.01" wire:model.defer="form.price_monthly" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="29.00">
                        @error('form.price_monthly')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-price-year">{{ __('Precio anual') }}</label>
                        <input id="tier-price-year" type="number" step="0.01" wire:model.defer="form.price_yearly" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="290.00">
                        @error('form.price_yearly')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-features">{{ __('Caracteristicas (separadas por coma)') }}</label>
                    <textarea id="tier-features" rows="2" wire:model.defer="featureString" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="coaching 1:1, eventos privados, biblioteca premium"></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="tier-color">{{ __('Color de identidad') }}</label>
                    <input id="tier-color" type="color" wire:model.defer="form.metadata_color" class="h-11 w-full rounded-xl border border-slate-700 bg-slate-950">
                    @error('form.metadata_color')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-6 pt-2 text-sm text-slate-200">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="form.is_default" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500"> {{ __('Predeterminado') }}
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="form.is_active" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-blue-500 focus:ring-blue-500"> {{ __('Activo') }}
                    </label>
                </div>
            </div>

            <div class="mt-auto flex items-center justify-end gap-3 pt-6">
                <button type="button" wire:click="createTier" class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-slate-500">{{ __('Cancelar') }}</button>
                <button type="submit" class="rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-400">{{ __('Guardar cambios') }}</button>
            </div>
        </form>
    </div>
</div>
