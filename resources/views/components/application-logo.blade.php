@php
    $branding = app(\App\Settings\BrandingSettings::class);
    $imageLogo = null;

    if ($branding->logo_mode === 'image') {
        if (!empty($branding->logo_horizontal_path)) {
            $imageLogo = \Illuminate\Support\Facades\Storage::disk('public')->url($branding->logo_horizontal_path);
        } elseif (!empty($branding->logo_url)) {
            $imageLogo = $branding->logo_url;
        }
    }
@endphp

@if($branding->logo_mode === 'text' && $branding->logo_svg)
    <span {{ $attributes->merge(['class' => 'flex items-center gap-1']) }} aria-label="Logo textual">
        {!! $branding->logo_svg !!}
    </span>
@elseif($imageLogo)
    <img src="{{ $imageLogo }}" alt="{{ $branding->logo_text ?: config('app.name') }}" {{ $attributes }}>
@else
    <span {{ $attributes->merge(['class' => 'font-semibold text-lg']) }}>
        {{ $branding->logo_text ?: config('app.name') }}
    </span>
@endif
