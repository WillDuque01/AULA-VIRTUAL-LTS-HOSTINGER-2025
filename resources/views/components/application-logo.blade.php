@php($branding = app(\App\Settings\BrandingSettings::class))

@if($branding->logo_mode === 'text' && $branding->logo_svg)
    <span {{ $attributes->merge(['class' => 'flex items-center gap-1']) }} aria-label="Logo textual">
        {!! $branding->logo_svg !!}
    </span>
@elseif($branding->logo_url)
    <img src="{{ $branding->logo_url }}" alt="{{ $branding->logo_text ?: config('app.name') }}" {{ $attributes }}>
@else
    <span {{ $attributes->merge(['class' => 'font-semibold text-lg']) }}>
        {{ $branding->logo_text ?: config('app.name') }}
    </span>
@endif
