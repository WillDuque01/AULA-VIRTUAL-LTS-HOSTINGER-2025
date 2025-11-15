@php($branding = \App\Support\Branding\Branding::info())
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branding['name'] }} · @yield('subject')</title>
    <style>
        body { margin: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; }
        a { color: #38bdf8; text-decoration: none; }
        .wrapper { width: 100%; padding: 24px 0; }
        .container { max-width: 620px; margin: 0 auto; background-color: #101c36; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(15,23,42,0.35); }
        .header { padding: 32px; text-align: center; background: linear-gradient(135deg,#1e3a8a,#312e81); }
        .header img { max-height: 60px; margin-bottom: 12px; }
        .content { padding: 32px; }
        .footer { padding: 24px; text-align: center; font-size: 12px; color: #94a3b8; }
        .btn { display: inline-block; padding: 14px 28px; border-radius: 999px; background-color: #38bdf8; color: #0f172a; font-weight: 600; margin-top: 24px; }
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
