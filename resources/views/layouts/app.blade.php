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
                --brand-radius: {{ $branding->border_radius }};
                --brand-font: {{ $branding->font_family }};
            }
            body {
                font-family: var(--brand-font), 'Figtree', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans antialiased {{ $branding->dark_mode ? 'dark' : '' }}">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-950">
            @include('layouts.navigation')

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
        @livewireScripts
    </body>
</html>
