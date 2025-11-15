<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BrandingSettings extends Settings
{
    public string $primary_color;
    public string $secondary_color;
    public string $accent_color;
    public string $neutral_color;
    public string $font_family;
    public string $body_font_family;
    public string $type_scale_ratio;
    public string $base_font_size;
    public string $line_height;
    public string $letter_spacing;
    public string $spacing_unit;
    public string $border_radius;
    public string $shadow_soft;
    public string $shadow_bold;
    public string $container_max_width;
    public bool $dark_mode;
    public string $logo_url;
    public string $logo_text;
    public string $logo_mode;
    public string $logo_svg;

    public static function group(): string
    {
        return 'branding';
    }
}

