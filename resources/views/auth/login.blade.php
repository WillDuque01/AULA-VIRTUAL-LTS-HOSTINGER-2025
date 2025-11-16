@php
    use Illuminate\Support\Facades\Route;

    $currentLocale = request()->route('locale') ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'es' ? 'en' : 'es';
    $routeParameters = request()->route()?->parameters() ?? [];
    $alternateRoute = route(Route::currentRouteName(), array_merge($routeParameters, ['locale' => $alternateLocale]));

    $roleMessages = [
        'admin' => __('Accede al panel administrativo para gestionar integraciones, DataPorter y branding.'),
        'teacher_admin' => __('Administra cohortes, aprueba contenido y coordina a tu equipo docente.'),
        'teacher' => __('Inicia sesión para enviar propuestas y gestionar tus módulos asignados.'),
        'student' => __('Ingresa para continuar tu progreso, prácticas y packs recomendados.'),
    ];
    $roleLabels = [
        'admin' => 'Admin',
        'teacher_admin' => 'Teacher Admin',
        'teacher' => 'Teacher Admin',
        'student' => 'Student',
    ];
@endphp

<x-guest-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Idioma') }}</p>
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="inline-flex rounded-full bg-slate-900/10 px-3 py-1 font-semibold text-slate-700">
                    {{ strtoupper($currentLocale) }}
                </span>
                <a href="{{ $alternateRoute }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-500 hover:border-slate-400 hover:text-slate-700">
                    {{ __('Cambiar a :locale', ['locale' => strtoupper($alternateLocale)]) }}
                </a>
            </div>
        </div>

        @if(!empty($targetRole) && isset($roleMessages[$targetRole]))
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 max-w-md">
                <p class="font-semibold">{{ __('Modo :role', ['role' => $roleLabels[$targetRole] ?? ucfirst($targetRole)]) }}</p>
                <p class="mt-1 text-indigo-800">{{ $roleMessages[$targetRole] }}</p>
            </div>
        @endif
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login', array_merge($routeParameters, ['locale' => $currentLocale])) }}">
        @csrf
        <input type="hidden" name="target_role" value="{{ $targetRole }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
