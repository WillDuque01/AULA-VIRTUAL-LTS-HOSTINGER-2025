@php
    $branding = app(\App\Settings\BrandingSettings::class);
    $brandName = $branding->logo_text ?: config('app.name', 'LetsTalkSpanish Academy');
    $primary = $branding->primary_color ?: '#0f172a';
    $neutral = $branding->neutral_color ?: '#f8fafc';
    $fontFamily = $branding->font_family ?: 'Inter, "Segoe UI", system-ui, sans-serif';
    $logoCandidate = $branding->logo_horizontal_path ?: $branding->logo_square_path;
    $logo = $branding->logo_url ?: asset('images/logo.png');

    if ($logoCandidate) {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($disk->exists($logoCandidate)) {
            $logo = $disk->url($logoCandidate);
        }
    }

    $locale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brandName }} · {{ __('landing.pending.meta_title') }}</title>
    <style>
        :root {
            --brand-primary: {{ $primary }};
            --brand-neutral: {{ $neutral }};
            --brand-font: {{ $fontFamily }};
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--brand-font);
            background: radial-gradient(circle at top, #fff, var(--brand-neutral));
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            width: min(560px, 100%);
            background: #fff;
            border-radius: 28px;
            padding: clamp(1.5rem, 4vw, 3rem);
            box-shadow: 0 20px 80px rgba(15,23,42,0.1);
            text-align: center;
        }
        .logo {
            max-width: 220px;
            margin: 0 auto 1.5rem;
        }
        h1 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            margin-bottom: 1rem;
            color: var(--brand-primary);
        }
        p {
            line-height: 1.6;
            color: rgba(15,23,42,0.75);
        }
        .actions {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .actions a {
            text-decoration: none;
            border-radius: 999px;
            padding: 0.9rem 1.6rem;
            font-weight: 600;
            border: 1px solid rgba(15,23,42,0.15);
            color: var(--brand-primary);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .actions a.primary {
            background: var(--brand-primary);
            color: #fff;
            border-color: var(--brand-primary);
            box-shadow: 0 15px 40px rgba(15,23,42,0.15);
        }
        .actions a:hover {
            transform: translateY(-2px);
        }
        .hint {
            margin-top: 1.5rem;
            font-size: .9rem;
            color: rgba(15,23,42,0.55);
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ $logo }}" alt="{{ __('landing.pending.logo_alt', ['brand' => $brandName]) }}" class="logo">
        <h1>{{ __('landing.pending.title') }}</h1>
        <p>
            {{ __('landing.pending.description') }}
        </p>
        <div class="actions">
            <a href="{{ route('login.admin', ['locale' => $locale]) }}" class="primary">
                {{ __('landing.pending.admin_cta') }}
            </a>
            <a href="{{ route('login', ['locale' => $locale]) }}">
                {{ __('landing.pending.login_cta') }}
            </a>
        </div>
        <div class="hint">
            {{ __('landing.pending.tip') }}
        </div>
    </div>
</body>
</html>

