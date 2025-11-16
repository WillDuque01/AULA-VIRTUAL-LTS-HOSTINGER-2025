<div class="space-y-6" x-data="brandingDesigner(@js($this->getId()), @entangle('dark_mode'), @entangle('logo_mode'))">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">Panel de branding 2030</h2>
            <p class="text-sm text-slate-500">Define tokens de color, tipografía, ritmos y logotipo. Todo se replica en tiempo real.</p>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model="dark_mode" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                <span class="text-slate-600 font-semibold">Modo oscuro por defecto</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase text-slate-400 tracking-wide">Max width</span>
                <input type="text" wire:model.defer="container_max_width" class="rounded-md border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1200px">
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Paleta principal</h3>
                <p class="text-sm text-slate-500">Setup cromático para hero, botones y superficies neutrales.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color primario</span>
                    <input type="color" wire:model="primary_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color secundario</span>
                    <input type="color" wire:model="secondary_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Color acento</span>
                    <input type="color" wire:model="accent_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Neutral</span>
                    <input type="color" wire:model="neutral_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Border radius base</span>
                    <input type="text" wire:model.defer="border_radius" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0.75rem">
                </label>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Tipografía y ritmo</h3>
                <p class="text-sm text-slate-500">Controla escalas, line-height y tracking para XS → 7XL.</p>
            </div>
            <div class="grid gap-4">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Heading font stack</span>
                    <input type="text" wire:model.defer="font_family" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder='Clash Display, "Space Grotesk"'>
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Body font stack</span>
                    <input type="text" wire:model.defer="body_font_family" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder='Inter, "Segoe UI", system-ui'>
                </label>
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo escala</span>
                        <input type="number" step="0.01" min="1" max="1.6" wire:model.defer="type_scale_ratio" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Base font size</span>
                        <select wire:model.defer="base_font_size" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="0.875rem">14px</option>
                            <option value="1rem">16px</option>
                            <option value="1.125rem">18px</option>
                        </select>
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Line height</span>
                        <input type="number" step="0.05" min="1.2" max="2" wire:model.defer="line_height" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Tracking</span>
                        <input type="text" wire:model.defer="letter_spacing" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0em / 0.02em">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Spacing unit</span>
                        <input type="text" wire:model.defer="spacing_unit" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0.5rem">
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Sombras y profundidad</h3>
                <p class="text-sm text-slate-500">Define presets suaves e intensos compatibles con UIX 2030.</p>
            </div>
            <div class="space-y-4">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Shadow soft</span>
                    <input type="text" wire:model.defer="shadow_soft" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0 24px 48px rgba(15,23,42,0.16)">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Shadow bold</span>
                    <input type="text" wire:model.defer="shadow_bold" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0 35px 65px rgba(15,23,42,0.28)">
                </label>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Logo y marca</h3>
                <p class="text-sm text-slate-500">Sube un logo en high-res o entrega un SVG tipográfico.</p>
            </div>
            <div class="space-y-4">
                <div class="flex flex-wrap gap-3 text-sm font-semibold text-slate-600">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" value="image" wire:model="logo_mode" class="text-blue-600 focus:ring-blue-500">
                        <span>Logo imagen</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" value="text" wire:model="logo_mode" class="text-blue-600 focus:ring-blue-500">
                        <span>Logo texto / SVG</span>
                    </label>
                </div>
                <div x-show="logoMode === 'image'" x-cloak class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Logo horizontal (3:1)</span>
                            <input type="file"
                                   x-ref="horizontalInput"
                                   @change.prevent="handleFileSelected($event, 'horizontal')"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            <p class="text-[11px] text-slate-400">Ideal para headers. PNG/SVG recomendado · máx 2MB.</p>
                            @error('logoHorizontalUpload') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Logo cuadrado (1:1)</span>
                            <input type="file"
                                   x-ref="squareInput"
                                   @change.prevent="handleFileSelected($event, 'square')"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            <p class="text-[11px] text-slate-400">Para favicon, apps o tarjetas. PNG/SVG · máx 2MB.</p>
                            @error('logoSquareUpload') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-xs uppercase text-slate-500">
                                <span>Vista actual horizontal</span>
                                <button type="button" wire:click="clearLogo('horizontal')" class="text-rose-500 font-semibold" @disabled(!$logo_horizontal_path && !$logo_url)>Reset</button>
                            </div>
                            @if($this->horizontalLogoUrl)
                                <img src="{{ $this->horizontalLogoUrl }}" alt="Logo horizontal" class="h-12 w-auto rounded-md bg-white/10 p-2">
                            @elseif($logo_url)
                                <img src="{{ $logo_url }}" alt="Logo" class="h-12 w-auto rounded-md bg-white/10 p-2">
                            @else
                                <p class="text-sm text-slate-400">Aún no hay logo subido. Puedes pegar una URL externa abajo.</p>
                            @endif
                        </div>
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-xs uppercase text-slate-500">
                                <span>Vista actual cuadrada</span>
                                <button type="button" wire:click="clearLogo('square')" class="text-rose-500 font-semibold" @disabled(!$logo_square_path)>Reset</button>
                            </div>
                            @if($this->squareLogoUrl)
                                <img src="{{ $this->squareLogoUrl }}" alt="Logo cuadrado" class="h-16 w-16 rounded-xl bg-white/10 p-2 object-contain">
                            @else
                                <p class="text-sm text-slate-400">Sube una versión cuadrada para favicons y avatares.</p>
                            @endif
                        </div>
                    </div>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Logo URL (opcional)</span>
                        <input type="url" wire:model.defer="logo_url" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="https://cdn.ejemplo.com/logo.svg">
                        <span class="text-[11px] text-slate-400">Se usará si prefieres un CDN externo o mientras subes la versión final.</span>
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Texto fallback</span>
                        <input type="text" wire:model.defer="logo_text" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Aula Virtual LTS">
                    </label>
                </div>
                <div x-show="logoMode === 'text'" x-cloak class="space-y-4">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Texto principal</span>
                        <input type="text" wire:model.defer="logo_text" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Aula LTS">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">SVG personalizado</span>
                        <textarea wire:model.defer="logo_svg" rows="4" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-xs" placeholder="<svg>...</svg>"></textarea>
                        <span class="text-[11px] text-slate-400">Pegue un SVG optimizado (máx 2KB). Se inyecta directo en el layout.</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 rounded-2xl shadow-inner p-6 text-white space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Preview interactiva</p>
                <h4 class="text-lg font-semibold">UIX 2030 — Hero + tarjetas con tus tokens</h4>
            </div>
            <button type="button" wire:click="save" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-white text-slate-900 rounded-full shadow hover:bg-slate-100">
                Guardar branding
                <span aria-hidden="true">↗</span>
            </button>
        </div>
        <div class="grid gap-4 lg:grid-cols-[2fr,1fr]">
            <div class="rounded-2xl p-6 space-y-5" style="background: linear-gradient(135deg, {{ $primary_color }}, {{ $secondary_color }}); border-radius: {{ $border_radius }}; box-shadow: {{ $shadow_soft }};">
                @php
                    $previewLogo = $this->horizontalLogoUrl ?? ($logo_url ?: null);
                @endphp
                <div class="flex items-center gap-3">
                    @if($logo_mode === 'image' && $previewLogo)
                        <img src="{{ $previewLogo }}" alt="Logo" class="h-10 w-auto rounded-md bg-white/10 p-1">
                    @elseif($logo_mode === 'text' && $logo_svg)
                        <div class="h-10 flex items-center" aria-label="Logo SVG">{!! $logo_svg !!}</div>
                    @else
                        <span class="text-xl font-bold">{{ $logo_text ?: 'Brand' }}</span>
                    @endif
                    <span class="text-xs uppercase tracking-wide text-white/70">{{ $font_family }}</span>
                </div>
                <div class="space-y-2" style="font-family: {{ $font_family }};">
                    <p class="text-sm text-white/70">Escala {{ $type_scale_ratio }} · Base {{ $base_font_size }} · Line height {{ $line_height }}</p>
                    <h2 class="text-3xl font-semibold">Cursos con microinteracciones celebratorias</h2>
                </div>
                <p class="text-base text-white/90" style="font-family: {{ $body_font_family }}; line-height: {{ $line_height }}; letter-spacing: {{ $letter_spacing }};">
                    Define tokens y plantillas en tiempo real. Las tarjetas, dashboards y player adoptan tu estilo con soporte light/dark y accesibilidad AA.
                </p>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="px-5 py-2 rounded-full font-semibold text-slate-900" style="background-color: {{ $accent_color }}; box-shadow: {{ $shadow_bold }};">
                        CTA principal
                    </button>
                    <button type="button" class="px-5 py-2 rounded-full border border-white/40 text-white font-semibold">
                        CTA secundario
                    </button>
                </div>
            </div>
            <div class="rounded-2xl bg-white/5 p-5 space-y-4" style="border-radius: {{ $border_radius }};">
                <p class="text-xs uppercase tracking-wide text-white/60">Tokens activos</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Neutral · {{ $neutral_color }}</li>
                    <li>Spacing unit · {{ $spacing_unit }}</li>
                    <li>Container max · {{ $container_max_width }}</li>
                    <li>Shadows · Soft {{ $shadow_soft }} / Bold {{ $shadow_bold }}</li>
                </ul>
                <div class="rounded-xl bg-white text-slate-900 p-4 shadow" style="box-shadow: {{ $shadow_soft }};">
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Tipografía</p>
                    <p class="text-base font-semibold" style="font-family: {{ $font_family }}">Display / headings</p>
                    <p class="text-sm" style="font-family: {{ $body_font_family }};">Body copy adaptable a XS–7XL con ratio {{ $type_scale_ratio }}.</p>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="cropping" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70 p-4">
        <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Recortar logo</p>
                    <h4 class="text-lg font-semibold text-slate-900" x-text="cropTarget === 'horizontal' ? 'Formato horizontal 3:1' : 'Formato cuadrado 1:1'"></h4>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="cancelCrop">
                    <span class="sr-only">Cerrar</span>
                    ✕
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="relative h-[60vh] w-full bg-slate-50" wire:ignore>
                    <img x-ref="cropImage" :src="cropImageSrc" class="max-h-full w-full object-contain" alt="Previsualización de recorte">
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button"
                            class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300"
                            @click="cancelCrop">
                        Cancelar
                    </button>
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                            @click="confirmCrop"
                            :disabled="uploading">
                        <span x-show="uploading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.css">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.js"></script>
        <script>
            function brandingDesigner(componentId, darkBinding = false, logoBinding = 'image') {
                return {
                    componentId,
                    previewDark: darkBinding,
                    logoMode: logoBinding,
                    cropping: false,
                    cropTarget: null,
                    cropImageSrc: null,
                    cropper: null,
                    uploading: false,
                    pendingInput: null,
                    handleFileSelected(event, variant) {
                        const file = event.target.files[0];
                        if (!file) {
                            return;
                        }

                        this.pendingInput = event.target;
                        this.cropTarget = variant;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.cropImageSrc = e.target.result;
                            this.openCropper();
                        };
                        reader.readAsDataURL(file);
                    },
                    openCropper() {
                        this.cropping = true;
                        this.$nextTick(() => {
                            if (this.cropper) {
                                this.cropper.destroy();
                            }
                            const aspect = this.cropTarget === 'horizontal' ? 3 / 1 : 1 / 1;
                            this.cropper = new Cropper(this.$refs.cropImage, {
                                aspectRatio: aspect,
                                viewMode: 2,
                                autoCropArea: 1,
                                background: false,
                                responsive: true,
                            });
                        });
                    },
                    cancelCrop(clearOnlyInput = false) {
                        this.resetCropper(clearOnlyInput);
                    },
                    confirmCrop() {
                        if (!this.cropper) {
                            return;
                        }

                        const dimensions = this.cropTarget === 'horizontal'
                            ? { width: 1200, height: 400 }
                            : { width: 600, height: 600 };

                        this.uploading = true;
                        this.cropper.getCroppedCanvas(dimensions).toBlob((blob) => {
                            if (!blob) {
                                this.uploading = false;
                                alert('No se pudo generar el recorte.');
                                return;
                            }

                            const file = new File([blob], `logo-${this.cropTarget}.png`, { type: 'image/png' });
                            this.uploadCropped(file);
                        }, 'image/png', 0.92);
                    },
                    uploadCropped(file) {
                        const field = this.cropTarget === 'horizontal' ? 'logoHorizontalUpload' : 'logoSquareUpload';

                        Livewire.find(this.componentId).upload(field, file, () => {
                            this.uploading = false;
                            this.resetCropper(true);
                            if (window.toast) {
                                toast('Recorte listo ✔️');
                            }
                        }, () => {
                            this.uploading = false;
                            alert('No se pudo subir el recorte. Intenta nuevamente.');
                            this.resetCropper(true);
                        });
                    },
                    resetCropper(clearInput = false) {
                        if (clearInput && this.pendingInput) {
                            this.pendingInput.value = '';
                        }
                        this.pendingInput = null;
                        this.cropping = false;
                        this.cropTarget = null;
                        this.cropImageSrc = null;
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                        this.uploading = false;
                    },
                };
            }
        </script>
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
