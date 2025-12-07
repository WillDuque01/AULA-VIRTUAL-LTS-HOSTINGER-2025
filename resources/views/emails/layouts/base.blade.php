@php
    $branding = \App\Support\Branding\Branding::info();
    try {
        $brandingSettings = app(\App\Settings\BrandingSettings::class);
    } catch (\Throwable $e) {
        $brandingSettings = null;
    }
    $emailPalette = [
        'primary' => $brandingSettings->primary_color ?? '#0f172a',
        'secondary' => $brandingSettings->secondary_color ?? '#1d4ed8',
        'accent' => $brandingSettings->accent_color ?? '#14b8a6',
        'background' => '#f8fafc',
        'surface' => '#ffffff',
        'text' => '#334155',
        'muted' => '#64748b',
        'border' => '#e2e8f0',
        'panel_bg' => '#f8fafc',
        'success' => '#0f766e',
    ];
    view()->share('emailPalette', $emailPalette);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branding['name'] }} · @yield('subject')</title>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            background-color: {{ $emailPalette['background'] }};
            color: {{ $emailPalette['text'] }};
        }
        a { color: {{ $emailPalette['accent'] }}; text-decoration: none; }
        .wrapper { width: 100%; padding: 24px 0; }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background-color: {{ $emailPalette['surface'] }};
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid {{ $emailPalette['border'] }};
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
        }
        .header {
            padding: 32px;
            text-align: center;
            background: {{ $emailPalette['primary'] }};
        }
        .header img { max-height: 48px; margin-bottom: 12px; }
        .header h1 { color: #fff; }
        .content { padding: 32px; }
        .content p { color: {{ $emailPalette['text'] }}; }
        .content p.muted { color: {{ $emailPalette['muted'] }}; }
        .footer {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: {{ $emailPalette['muted'] }};
            background: {{ $emailPalette['background'] }};
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 999px;
            background-color: {{ $emailPalette['accent'] }};
            color: #fff;
            font-weight: 600;
            margin-top: 24px;
            box-shadow: 0 10px 20px rgba(20, 184, 166, 0.25);
        }
        @media (max-width: 600px) {
            .container { margin: 0 16px; }
            .content { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}">
                <h1 style="margin: 0; font-size: 24px; color: #e0f2fe;">@yield('title')</h1>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p style="margin: 4px 0;">{{ $branding['name'] }}</p>
                @if($branding['support_email'])
                    <p style="margin: 4px 0;">{{ $branding['support_email'] }}</p>
                @endif
                @if($branding['support_phone'])
                    <p style="margin: 4px 0;">{{ $branding['support_phone'] }}</p>
                @endif
                @if($branding['support_address'])
                    <p style="margin: 4px 0;">{{ $branding['support_address'] }}</p>
                @endif
                <p style="margin: 12px 0;">
                    <a href="{{ $branding['website'] }}">{{ $branding['website'] }}</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
