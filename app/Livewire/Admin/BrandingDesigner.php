<?php

namespace App\Livewire\Admin;

use App\Settings\BrandingSettings;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BrandingDesigner extends Component
{
    public string $primary_color = '#0f172a';
    public string $secondary_color = '#1d4ed8';
    public string $accent_color = '#f97316';
    public string $font_family = 'Inter';
    public string $border_radius = '0.75rem';
    public bool $dark_mode = false;
    public string $logo_url = '';
    public string $logo_text = '';

    public function mount(BrandingSettings $settings): void
    {
        $this->primary_color = $settings->primary_color;
        $this->secondary_color = $settings->secondary_color;
        $this->accent_color = $settings->accent_color;
        $this->font_family = $settings->font_family;
        $this->border_radius = $settings->border_radius;
        $this->dark_mode = $settings->dark_mode;
        $this->logo_url = $settings->logo_url;
        $this->logo_text = $settings->logo_text;
    }

    public function save(BrandingSettings $settings): void
    {
        $data = $this->validate([
            'primary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'secondary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'accent_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'font_family' => ['required', 'string', 'max:255'],
            'border_radius' => ['required', 'string', 'max:32'],
            'dark_mode' => ['boolean'],
            'logo_url' => ['nullable', 'url'],
            'logo_text' => ['nullable', 'string', 'max:80'],
        ]);

        $settings->primary_color = $data['primary_color'];
        $settings->secondary_color = $data['secondary_color'];
        $settings->accent_color = $data['accent_color'];
        $settings->font_family = $data['font_family'];
        $settings->border_radius = $data['border_radius'];
        $settings->dark_mode = $data['dark_mode'];
        $settings->logo_url = $data['logo_url'] ?? '';
        $settings->logo_text = $data['logo_text'] ?? '';
        $settings->save();

        cache()->forget('branding.info');

        $this->dispatchBrowserEvent('branding-saved');
        $this->dispatch('branding-updated');
    }

    public function render()
    {
        return view('livewire.admin.branding-designer');
    }
}
