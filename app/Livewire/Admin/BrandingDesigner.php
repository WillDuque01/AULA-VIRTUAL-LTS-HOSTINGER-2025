<?php

namespace App\Livewire\Admin;

use App\Settings\BrandingSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class BrandingDesigner extends Component
{
    use WithFileUploads;

    public string $primary_color = '#0f172a';
    public string $secondary_color = '#1d4ed8';
    public string $accent_color = '#f97316';
    public string $neutral_color = '#cbd5f5';
    public string $font_family = 'Inter';
    public string $body_font_family = 'Inter';
    public string $type_scale_ratio = '1.125';
    public string $base_font_size = '1rem';
    public string $line_height = '1.6';
    public string $letter_spacing = '0em';
    public string $spacing_unit = '0.5rem';
    public string $border_radius = '0.75rem';
    public string $shadow_soft = '0 25px 55px rgba(15,23,42,0.15)';
    public string $shadow_bold = '0 35px 65px rgba(15,23,42,0.25)';
    public string $container_max_width = '1200px';
    public bool $dark_mode = false;
    public string $logo_url = '';
    public string $logo_text = '';
    public string $logo_mode = 'image';
    public string $logo_svg = '';
    public string $logo_horizontal_path = '';
    public string $logo_square_path = '';
    public $logoHorizontalUpload;
    public $logoSquareUpload;

    public function mount(BrandingSettings $settings): void
    {
        $this->primary_color = $settings->primary_color;
        $this->secondary_color = $settings->secondary_color;
        $this->accent_color = $settings->accent_color;
        $this->neutral_color = $settings->neutral_color;
        $this->font_family = $settings->font_family;
        $this->body_font_family = $settings->body_font_family;
        $this->type_scale_ratio = $settings->type_scale_ratio;
        $this->base_font_size = $settings->base_font_size;
        $this->line_height = $settings->line_height;
        $this->letter_spacing = $settings->letter_spacing;
        $this->spacing_unit = $settings->spacing_unit;
        $this->border_radius = $settings->border_radius;
        $this->shadow_soft = $settings->shadow_soft;
        $this->shadow_bold = $settings->shadow_bold;
        $this->container_max_width = $settings->container_max_width;
        $this->dark_mode = $settings->dark_mode;
        $this->logo_url = $settings->logo_url;
        $this->logo_text = $settings->logo_text;
        $this->logo_mode = $settings->logo_mode;
        $this->logo_svg = $settings->logo_svg;
        $this->logo_horizontal_path = $settings->logo_horizontal_path ?? '';
        $this->logo_square_path = $settings->logo_square_path ?? '';
    }

    public function save(BrandingSettings $settings): void
    {
        $data = $this->validate([
            'primary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'secondary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'accent_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'neutral_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'font_family' => ['required', 'string', 'max:255'],
            'body_font_family' => ['required', 'string', 'max:255'],
            'type_scale_ratio' => ['required', 'numeric', 'min:1', 'max:1.6'],
            'base_font_size' => ['required', Rule::in(['0.875rem', '1rem', '1.125rem'])],
            'line_height' => ['required', 'numeric', 'min:1.2', 'max:2'],
            'letter_spacing' => ['required', 'string', 'max:8'],
            'spacing_unit' => ['required', 'string', 'max:8'],
            'border_radius' => ['required', 'string', 'max:32'],
            'shadow_soft' => ['required', 'string', 'max:255'],
            'shadow_bold' => ['required', 'string', 'max:255'],
            'container_max_width' => ['required', 'string', 'max:12'],
            'dark_mode' => ['boolean'],
            'logo_url' => ['nullable', 'url'],
            'logo_text' => ['nullable', 'string', 'max:80'],
            'logo_mode' => ['required', Rule::in(['image', 'text'])],
            'logo_svg' => ['nullable', 'string', 'max:2048', Rule::requiredIf(fn () => $this->logo_mode === 'text')],
            'logoHorizontalUpload' => ['nullable', 'file', 'max:2048', 'mimetypes:image/png,image/jpeg,image/webp,image/svg+xml'],
            'logoSquareUpload' => ['nullable', 'file', 'max:2048', 'mimetypes:image/png,image/jpeg,image/webp,image/svg+xml'],
        ]);

        $logoPaths = $this->handleLogoUploads();

        if ($data['logo_mode'] === 'image' && !$this->hasImageLogo($logoPaths, $data['logo_url'])) {
            $this->addError('logo_url', __('branding.logo_required'));

            return;
        }

        $settings->primary_color = $data['primary_color'];
        $settings->secondary_color = $data['secondary_color'];
        $settings->accent_color = $data['accent_color'];
        $settings->neutral_color = $data['neutral_color'];
        $settings->font_family = $data['font_family'];
        $settings->body_font_family = $data['body_font_family'];
        $settings->type_scale_ratio = (string) $data['type_scale_ratio'];
        $settings->base_font_size = $data['base_font_size'];
        $settings->line_height = (string) $data['line_height'];
        $settings->letter_spacing = $data['letter_spacing'];
        $settings->spacing_unit = $data['spacing_unit'];
        $settings->border_radius = $data['border_radius'];
        $settings->shadow_soft = $data['shadow_soft'];
        $settings->shadow_bold = $data['shadow_bold'];
        $settings->container_max_width = $data['container_max_width'];
        $settings->dark_mode = $data['dark_mode'];
        $settings->logo_url = $data['logo_url'] ?? '';
        $settings->logo_text = $data['logo_text'] ?? '';
        $settings->logo_mode = $data['logo_mode'];
        $settings->logo_svg = $data['logo_svg'] ?? '';
        $settings->logo_horizontal_path = $logoPaths['horizontal'];
        $settings->logo_square_path = $logoPaths['square'];
        $settings->save();

        cache()->forget('branding.info');

        $this->dispatch('branding-saved');
        $this->dispatch('branding-updated');
    }

    public function clearLogo(string $variant): void
    {
        if ($variant === 'horizontal' && $this->logo_horizontal_path) {
            Storage::disk('public')->delete($this->logo_horizontal_path);
            $this->logo_horizontal_path = '';
        }

        if ($variant === 'square' && $this->logo_square_path) {
            Storage::disk('public')->delete($this->logo_square_path);
            $this->logo_square_path = '';
        }
    }

    public function getHorizontalLogoUrlProperty(): ?string
    {
        return $this->logo_horizontal_path
            ? Storage::disk('public')->url($this->logo_horizontal_path)
            : null;
    }

    public function getSquareLogoUrlProperty(): ?string
    {
        return $this->logo_square_path
            ? Storage::disk('public')->url($this->logo_square_path)
            : null;
    }

    private function handleLogoUploads(): array
    {
        $currentHorizontal = $this->logo_horizontal_path;
        $currentSquare = $this->logo_square_path;

        if ($this->logoHorizontalUpload) {
            if ($currentHorizontal) {
                Storage::disk('public')->delete($currentHorizontal);
            }

            $currentHorizontal = $this->logoHorizontalUpload->storePublicly('branding', 'public');
        }

        if ($this->logoSquareUpload) {
            if ($currentSquare) {
                Storage::disk('public')->delete($currentSquare);
            }

            $currentSquare = $this->logoSquareUpload->storePublicly('branding', 'public');
        }

        $this->logo_horizontal_path = $currentHorizontal ?? '';
        $this->logo_square_path = $currentSquare ?? '';
        $this->logoHorizontalUpload = null;
        $this->logoSquareUpload = null;

        return [
            'horizontal' => $this->logo_horizontal_path,
            'square' => $this->logo_square_path,
        ];
    }

    private function hasImageLogo(array $paths, ?string $remoteUrl): bool
    {
        return filled($paths['horizontal']) || filled($paths['square']) || filled($remoteUrl);
    }

    public function render()
    {
        return view('livewire.admin.branding-designer');
    }
}
