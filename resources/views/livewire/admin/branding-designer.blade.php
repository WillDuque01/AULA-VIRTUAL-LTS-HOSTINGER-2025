<div class="space-y-6" x-data="{ previewDark: @entangle('dark_mode') }">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">Branding y tema</h2>
            <p class="text-sm text-slate-500">Ajusta paleta, tipografía y logotipo. Los cambios se aplican al guardar.</p>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span class="font-semibold text-slate-600">Modo oscuro por defecto</span>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model="dark_mode" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                <span class="text-xs text-slate-500">Activar</span>
            </label>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Paleta y tipografía</h3>
                <p class="text-sm text-slate-500">Define los tokens básicos del sistema de diseño.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color primario</label>
                    <input type="color" wire:model="primary_color" class="mt-2 h-12 w-full rounded-md border border-slate-200 p-1">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color secundario</label>
                    <input type="color" wire:model="secondary_color" class="mt-2 h-12 w-full rounded-md border border-slate-200 p-1">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color acento</label>
                    <input type="color" wire:model="accent_color" class="mt-2 h-12 w-full rounded-md border border-slate-200 p-1">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Radio base</label>
                    <input type="text" wire:model.defer="border_radius" class="mt-2 block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej. 0.75rem">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Font family</label>
                    <input type="text" wire:model.defer="font_family" class="mt-2 block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder='Inter, "Segoe UI", system-ui'>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Identidad visual</h3>
                <p class="text-sm text-slate-500">Define logo y texto de marca.</p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Logo URL</label>
                    <input type="url" wire:model.defer="logo_url" class="mt-2 block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="https://cdn.ejemplo.com/logo.svg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Texto / fallback</label>
                    <input type="text" wire:model.defer="logo_text" class="mt-2 block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Aula Virtual LTS">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 rounded-2xl shadow-inner p-6 text-white space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Preview</p>
                <h4 class="text-lg font-semibold">Hero principal</h4>
            </div>
            <button type="button" wire:click="save" class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-white text-slate-900 rounded-full shadow hover:bg-slate-100">
                Guardar branding
            </button>
        </div>
        <div class="rounded-xl p-6 space-y-4" style="background: linear-gradient(135deg, {{ $primary_color }}, {{ $secondary_color }}); border-radius: {{ $border_radius }};">
            <div class="flex items-center gap-3">
                @if($logo_url)
                    <img src="{{ $logo_url }}" alt="Logo" class="h-10 w-auto rounded-md bg-white/10 p-1">
                @else
                    <span class="text-xl font-bold">{{ $logo_text ?: 'Brand' }}</span>
                @endif
                <span class="text-sm uppercase tracking-wide text-white/70">{{ $font_family }}</span>
            </div>
            <p class="text-base text-white/90">Así se verá el header y CTAs usando tu configuración actual. Ajusta colores y tipografía hasta lograr el estilo deseado.</p>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="px-4 py-2 rounded-full font-semibold text-slate-900 shadow" style="background-color: {{ $accent_color }}">CTA principal</button>
                <button type="button" class="px-4 py-2 rounded-full border border-white/30 text-white font-semibold">CTA secundario</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.addEventListener('branding-saved', () => {
                if (window.toast) {
                    toast('Branding actualizado ✔️');
                } else {
                    alert('Branding actualizado');
                }
            });
        </script>
    @endpush
@endonce
