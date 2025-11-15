<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BrandingSettings extends Settings
{
    public string $primary_color;
    public string $secondary_color;
    public string $accent_color;
    public string $font_family;
    public string $border_radius;
    public bool $dark_mode;
    public string $logo_url;
    public string $logo_text;

    public static function group(): string
    {
        return 'branding';
    }
}

