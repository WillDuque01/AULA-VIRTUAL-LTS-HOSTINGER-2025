<!DOCTYPE html>
@php($branding = app(\App\Settings\BrandingSettings::class))
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            :root {
                --brand-primary: {{ $branding->primary_color }};
                --brand-secondary: {{ $branding->secondary_color }};
                --brand-accent: {{ $branding->accent_color }};
                --brand-neutral: {{ $branding->neutral_color }};
                --brand-radius: {{ $branding->border_radius }};
                --brand-font: {{ $branding->font_family }};
                --brand-heading-font: {{ $branding->font_family }};
                --brand-body-font: {{ $branding->body_font_family }};
                --brand-type-scale: {{ $branding->type_scale_ratio }};
                --brand-base-font-size: {{ $branding->base_font_size }};
                --brand-line-height: {{ $branding->line_height }};
                --brand-letter-spacing: {{ $branding->letter_spacing }};
                --brand-spacing-unit: {{ $branding->spacing_unit }};
                --brand-shadow-soft: {{ $branding->shadow_soft }};
                --brand-shadow-bold: {{ $branding->shadow_bold }};
                --brand-container-width: {{ $branding->container_max_width }};
            }
            body {
                font-family: var(--brand-body-font, var(--brand-font)), 'Figtree', sans-serif;
                font-size: var(--brand-base-font-size, 1rem);
                line-height: var(--brand-line-height, 1.5);
                letter-spacing: var(--brand-letter-spacing, 0);
            }
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--brand-heading-font, var(--brand-font)), 'Figtree', sans-serif;
                letter-spacing: calc(var(--brand-letter-spacing, 0) * 0.75);
            }
        </style>
        <?php
            $route = request()->route();
            $routeName = $route ? $route->getName() : null;
            $routeParams = $route ? $route->parameters() : [];
        ?>
        @if($routeName && isset($routeParams['locale']))
            @foreach(['es','en'] as $localeOption)
                @php($altParams = $routeParams)
                @php($altParams['locale'] = $localeOption)
                <link rel="alternate" hreflang="{{ $localeOption }}" href="{{ route($routeName, $altParams) }}">
            @endforeach
            <link rel="canonical" href="{{ route($routeName, $routeParams) }}">
        @endif
    </head>
    <body class="font-sans antialiased {{ $branding->dark_mode ? 'dark' : '' }}">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-950">
            @include('layouts.navigation')
            @auth
                @php($__profileSummary = auth()->user()->profileSummary())
                @if(!($__profileSummary['is_complete'] ?? false))
                    <span class="sr-only" aria-hidden="true">{{ __('Completa tu perfil') }}</span>
                @endif
                @if(auth()->user()->hasAnyRole(['teacher','teacher_admin','Profesor']))
                    <!-- Teacher profile -->
                @endif
                <livewire:profile.completion-banner />
            @endauth

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @php
            $audience = \App\Support\Guides\GuideRegistry::audienceFromUser(auth()->user());
            $floatingGuides = \App\Support\Guides\GuideRegistry::routeGuides($routeName ?? null, $audience);
        @endphp
        @if(!empty($floatingGuides))
            <x-help.floating :cards="$floatingGuides" />
        @endif
        @livewireScripts
    </body>
</html>
