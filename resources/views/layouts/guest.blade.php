<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @hasSection('title')
                @yield('title') · {{ config('app.name', 'Laravel') }}
            @else
                {{ config('app.name', 'Laravel') }}
            @endif
        </title>

        <!-- Fonts -->
        {{-- [AGENTE: OPUS 4.5] - Fix: Cargar Inter y Onest según UIX 2030 (tailwind.config.js) --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=onest:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $guestOuterClass = trim($__env->yieldContent('guest_outer_class', 'min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100'));
            $guestCardClass = trim($__env->yieldContent('guest_card_class', 'w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg'));
            $guestShowLogo = strtolower(trim($__env->yieldContent('guest_show_logo', 'true')));
            $shouldShowLogo = !in_array($guestShowLogo, ['false', '0', 'off'], true);
        @endphp

        <div class="{{ $guestOuterClass }}">
            @if($shouldShowLogo)
                <div>
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>
            @endif

            <div class="{{ $guestCardClass }}">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </div>
    </body>
</html>
