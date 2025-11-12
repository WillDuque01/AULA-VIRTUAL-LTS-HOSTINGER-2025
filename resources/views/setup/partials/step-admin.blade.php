<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold">{{ __('Crear cuenta administrativa') }}</h2>
        <p class="text-sm text-slate-400">{{ __('Este usuario tendrá control total de la plataforma y podrá invitar a otros miembros del equipo.') }}</p>
    </div>

    @if($adminCreated)
        <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ __('Ya existe un administrador. Si deseas crear otro, podrás hacerlo más adelante desde el dashboard.') }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="text-xs font-semibold uppercase text-slate-400">{{ __('Nombre completo') }}</label>
            <input type="text" wire:model.defer="admin.name" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:ring-blue-500" {{ $adminCreated ? 'disabled' : '' }}>
            @error('admin.name')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">{{ __('Correo electrónico') }}</label>
            <input type="email" wire:model.defer="admin.email" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:ring-blue-500" {{ $adminCreated ? 'disabled' : '' }}>
            @error('admin.email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">{{ __('Contraseña') }}</label>
            <input type="password" wire:model.defer="admin.password" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:ring-blue-500" {{ $adminCreated ? 'disabled' : '' }}>
            @error('admin.password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">{{ __('Confirmar contraseña') }}</label>
            <input type="password" wire:model.defer="admin.password_confirmation" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:ring-blue-500" {{ $adminCreated ? 'disabled' : '' }}>
        </div>
    </div>
</div>
