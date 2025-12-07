<div class="space-y-6" x-data="brandingDesigner(@js($this->getId()), @entangle('dark_mode'), @entangle('logo_mode'))">
    @php
        $brandingText = fn (string $key, array $replace = []) => __('branding.'.$key, $replace);
    @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">{{ $brandingText('panel.title') }}</h2>
            <p class="text-sm text-slate-500">{{ $brandingText('panel.description') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model="dark_mode" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                <span class="text-slate-600 font-semibold">{{ $brandingText('panel.dark_mode') }}</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase text-slate-400 tracking-wide">{{ $brandingText('panel.max_width') }}</span>
                <input type="text" wire:model.defer="container_max_width" class="rounded-md border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1200px">
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $brandingText('palette.title') }}</h3>
                <p class="text-sm text-slate-500">{{ $brandingText('palette.description') }}</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('palette.primary') }}</span>
                    <input type="color" wire:model="primary_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('palette.secondary') }}</span>
                    <input type="color" wire:model="secondary_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('palette.accent') }}</span>
                    <input type="color" wire:model="accent_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('palette.neutral') }}</span>
                    <input type="color" wire:model="neutral_color" class="h-12 w-full rounded-md border border-slate-200 p-1">
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('palette.radius') }}</span>
                    <input type="text" wire:model.defer="border_radius" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0.75rem">
                </label>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $brandingText('typography.title') }}</h3>
                <p class="text-sm text-slate-500">{{ $brandingText('typography.description') }}</p>
            </div>
            <div class="grid gap-4">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.heading_font') }}</span>
                    <input type="text" wire:model.defer="font_family" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder='Clash Display, "Space Grotesk"'>
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.body_font') }}</span>
                    <input type="text" wire:model.defer="body_font_family" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder='Inter, "Segoe UI", system-ui'>
                </label>
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.scale_ratio') }}</span>
                        <input type="number" step="0.01" min="1" max="1.6" wire:model.defer="type_scale_ratio" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.base_size') }}</span>
                        <select wire:model.defer="base_font_size" class="block w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="0.875rem">14px</option>
                            <option value="1rem">16px</option>
                            <option value="1.125rem">18px</option>
                        </select>
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.line_height') }}</span>
                        <input type="number" step="0.05" min="1.2" max="2" wire:model.defer="line_height" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.tracking') }}</span>
                        <input type="text" wire:model.defer="letter_spacing" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0em / 0.02em">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('typography.spacing') }}</span>
                        <input type="text" wire:model.defer="spacing_unit" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0.5rem">
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $brandingText('shadows.title') }}</h3>
                <p class="text-sm text-slate-500">{{ $brandingText('shadows.description') }}</p>
            </div>
            <div class="space-y-4">
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('shadows.soft') }}</span>
                    <input type="text" wire:model.defer="shadow_soft" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0 24px 48px rgba(15,23,42,0.16)">
                </label>
                <label class="space-y-2">
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('shadows.bold') }}</span>
                    <input type="text" wire:model.defer="shadow_bold" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="0 35px 65px rgba(15,23,42,0.28)">
                </label>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $brandingText('logo.title') }}</h3>
                <p class="text-sm text-slate-500">{{ $brandingText('logo.description') }}</p>
            </div>
            <div class="space-y-4">
                <div class="flex flex-wrap gap-3 text-sm font-semibold text-slate-600">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" value="image" wire:model="logo_mode" class="text-blue-600 focus:ring-blue-500">
                        <span>{{ $brandingText('logo.image_option') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" value="text" wire:model="logo_mode" class="text-blue-600 focus:ring-blue-500">
                        <span>{{ $brandingText('logo.text_option') }}</span>
                    </label>
                </div>
                <div x-show="logoMode === 'image'" x-cloak class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.horizontal') }}</span>
                            <input type="file"
                                   x-ref="horizontalInput"
                                   @change.prevent="handleFileSelected($event, 'horizontal')"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            <p class="text-[11px] text-slate-400">{{ $brandingText('logo.horizontal_hint') }}</p>
                            @error('logoHorizontalUpload') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.square') }}</span>
                            <input type="file"
                                   x-ref="squareInput"
                                   @change.prevent="handleFileSelected($event, 'square')"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                            <p class="text-[11px] text-slate-400">{{ $brandingText('logo.square_hint') }}</p>
                            @error('logoSquareUpload') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                        </label>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-xs uppercase text-slate-500">
                                <span>{{ $brandingText('logo.current_horizontal') }}</span>
                                <button type="button" wire:click="clearLogo('horizontal')" class="text-rose-500 font-semibold" @disabled(!$logo_horizontal_path && !$logo_url)>Reset</button>
                            </div>
                            @if($this->horizontalLogoUrl)
                                <img src="{{ $this->horizontalLogoUrl }}" alt="Logo horizontal" class="h-12 w-auto rounded-md bg-white/10 p-2">
                            @elseif($logo_url)
                                <img src="{{ $logo_url }}" alt="Logo" class="h-12 w-auto rounded-md bg-white/10 p-2">
                            @else
                                <p class="text-sm text-slate-400">{{ $brandingText('logo.empty_logo') }}</p>
                            @endif
                        </div>
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-xs uppercase text-slate-500">
                                <span>{{ $brandingText('logo.current_square') }}</span>
                                <button type="button" wire:click="clearLogo('square')" class="text-rose-500 font-semibold" @disabled(!$logo_square_path)>Reset</button>
                            </div>
                            @if($this->squareLogoUrl)
                                <img src="{{ $this->squareLogoUrl }}" alt="Logo cuadrado" class="h-16 w-16 rounded-xl bg-white/10 p-2 object-contain">
                            @else
                                <p class="text-sm text-slate-400">{{ $brandingText('logo.empty_square') }}</p>
                            @endif
                        </div>
                    </div>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.logo_url') }}</span>
                        <input type="url" wire:model.defer="logo_url" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="https://cdn.ejemplo.com/logo.svg">
                        <span class="text-[11px] text-slate-400">{{ $brandingText('logo.logo_url_hint') }}</span>
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.fallback_text') }}</span>
                        <input type="text" wire:model.defer="logo_text" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Aula Virtual LTS">
                    </label>
                </div>
                <div x-show="logoMode === 'text'" x-cloak class="space-y-4">
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.text_primary') }}</span>
                        <input type="text" wire:model.defer="logo_text" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Aula LTS">
                    </label>
                    <label class="space-y-2">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $brandingText('logo.custom_svg') }}</span>
                        <textarea wire:model.defer="logo_svg" rows="4" class="block w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-xs" placeholder="<svg>...</svg>"></textarea>
                        <span class="text-[11px] text-slate-400">{{ $brandingText('logo.custom_svg_hint') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 rounded-2xl shadow-inner p-6 text-white space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $brandingText('preview.badge') }}</p>
                <h4 class="text-lg font-semibold">{{ $brandingText('preview.title') }}</h4>
            </div>
            <button type="button" wire:click="save" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-white text-slate-900 rounded-full shadow hover:bg-slate-100">
                {{ $brandingText('preview.save') }}
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
                    <p class="text-sm text-white/70">{{ $brandingText('preview.scale_meta', ['ratio' => $type_scale_ratio, 'size' => $base_font_size, 'line' => $line_height]) }}</p>
                    <h2 class="text-3xl font-semibold">{{ $brandingText('preview.title') }}</h2>
                </div>
                <p class="text-base text-white/90" style="font-family: {{ $body_font_family }}; line-height: {{ $line_height }}; letter-spacing: {{ $letter_spacing }};">
                    {{ $brandingText('preview.description') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="px-5 py-2 rounded-full font-semibold text-slate-900" style="background-color: {{ $accent_color }}; box-shadow: {{ $shadow_bold }};">
                        {{ $brandingText('preview.cta_primary') }}
                    </button>
                    <button type="button" class="px-5 py-2 rounded-full border border-white/40 text-white font-semibold">
                        {{ $brandingText('preview.cta_secondary') }}
                    </button>
                </div>
            </div>
            <div class="rounded-2xl bg-white/5 p-5 space-y-4" style="border-radius: {{ $border_radius }};">
                <p class="text-xs uppercase tracking-wide text-white/60">{{ $brandingText('tokens.title') }}</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>{{ $brandingText('tokens.neutral', ['value' => $neutral_color]) }}</li>
                    <li>{{ $brandingText('tokens.spacing', ['value' => $spacing_unit]) }}</li>
                    <li>{{ $brandingText('tokens.container', ['value' => $container_max_width]) }}</li>
                    <li>{{ $brandingText('tokens.shadows', ['soft' => $shadow_soft, 'bold' => $shadow_bold]) }}</li>
                </ul>
                <div class="rounded-xl bg-white text-slate-900 p-4 shadow" style="box-shadow: {{ $shadow_soft }};">
                    <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">{{ $brandingText('tokens.typography_badge') }}</p>
                    <p class="text-base font-semibold" style="font-family: {{ $font_family }}">{{ $brandingText('tokens.typography_heading') }}</p>
                    <p class="text-sm" style="font-family: {{ $body_font_family }};">{{ $brandingText('tokens.typography_body', ['ratio' => $type_scale_ratio]) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="cropping" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70 p-4">
        <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $brandingText('cropping.title') }}</p>
                    <h4 class="text-lg font-semibold text-slate-900" x-text="cropTarget === 'horizontal' ? '{{ $brandingText('cropping.horizontal') }}' : '{{ $brandingText('cropping.square') }}'"></h4>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="cancelCrop">
                    <span class="sr-only">{{ $brandingText('cropping.close') }}</span>
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
                        {{ $brandingText('cropping.cancel') }}
                    </button>
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                            @click="confirmCrop"
                            :disabled="uploading">
                        <span x-show="uploading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        {{ $brandingText('cropping.apply') }}
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
                    messages: {
                        cropError: @json($brandingText('cropping.error')),
                        uploadError: @json($brandingText('cropping.upload_error')),
                        success: @json($brandingText('cropping.success')),
                    },
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
                                alert(this.messages.cropError);
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
                                toast(this.messages.success);
                            }
                        }, () => {
                            this.uploading = false;
                            alert(this.messages.uploadError);
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
