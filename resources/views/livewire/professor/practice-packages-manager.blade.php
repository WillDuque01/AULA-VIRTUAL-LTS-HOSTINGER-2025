<div class="space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Paquetes premium</p>
                <h3 class="text-lg font-semibold text-slate-900">Crea packs de prácticas</h3>
                <p class="text-xs text-slate-500">Define sesiones en vivo, precio y plataforma (Discord por defecto).</p>
            </div>
        </div>
        <form wire:submit.prevent="savePackage" class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="space-y-1 text-sm text-slate-600">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Título</span>
                <input type="text" wire:model.defer="form.title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Pack Conversación B2">
            </label>
            <label class="space-y-1 text-sm text-slate-600">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Subtítulo</span>
                <input type="text" wire:model.defer="form.subtitle" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="3 sesiones enfocadas en pronunciación">
            </label>
            <label class="space-y-1 text-sm text-slate-600 md:col-span-2">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Descripción</span>
                <textarea wire:model.defer="form.description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Incluye feedback 1:1, guía personalizada y resumen descargable."></textarea>
            </label>
            <div class="grid gap-4 sm:grid-cols-3 md:col-span-2">
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Sesiones</span>
                    <input type="number" min="1" wire:model.defer="form.sessions_count" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Precio</span>
                    <input type="number" min="0" step="0.01" wire:model.defer="form.price_amount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Moneda</span>
                    <input type="text" wire:model.defer="form.price_currency" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="USD">
                </label>
            </div>
            <label class="space-y-1 text-sm text-slate-600">
                <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Lección objetivo</span>
                <select wire:model.defer="form.lesson_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('Opcional') }}</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}">
                            {{ data_get($lesson->chapter?->course, 'slug') }} · {{ data_get($lesson->config, 'title', __('Lesson')) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">Plataforma</span>
                    <select wire:model.defer="form.delivery_platform" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="discord">Discord (recomendado)</option>
                        <option value="zoom">Zoom</option>
                        <option value="meet">Google Meet</option>
                        <option value="custom">Otro</option>
                    </select>
                </label>
                <label class="space-y-1 text-sm text-slate-600">
                    <span class="text-xs uppercase font-semibold tracking-wide text-slate-400">URL / Canal</span>
                    <input type="url" wire:model.defer="form.delivery_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://discord.gg/...">
                </label>
            </div>
            @if(auth()->user()?->hasAnyRole(['Admin', 'teacher_admin']))
                <div class="flex items-center gap-4 text-sm text-slate-600 md:col-span-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="form.is_global" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                        <span>Visible para todos los visitantes</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <span>Visibilidad</span>
                        <select wire:model.defer="form.visibility" class="rounded border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="private">Solo mis estudiantes</option>
                            <option value="public">Público</option>
                        </select>
                    </label>
                </div>
            @endif
            <div class="md:col-span-2 flex items-center justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Guardar paquete
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Mis paquetes</p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($packages as $package)
                <div class="px-6 py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $package->title }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $package->sessions_count }} sesiones · {{ number_format($package->price_amount, 2) }} {{ $package->price_currency }}
                            @if($package->is_global)
                                · 🌍 Global
                            @endif
                        </p>
                        <p class="text-xs text-slate-400">{{ ucfirst($package->status) }} · {{ $package->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if($package->status !== 'published')
                            <button wire:click="publish({{ $package->id }})" class="rounded-full border border-emerald-200 px-3 py-1 font-semibold text-emerald-700 hover:border-emerald-300">
                                Publicar
                            </button>
                        @endif
                        @if($package->status !== 'archived')
                            <button wire:click="archive({{ $package->id }})" class="rounded-full border border-slate-200 px-3 py-1 font-semibold text-slate-600 hover:border-slate-300">
                                Archivar
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-500">
                    Aún no has creado paquetes premium. Comienza con una oferta introductoria (3 sesiones).
                </div>
            @endforelse
        </div>
    </div>
</div>

