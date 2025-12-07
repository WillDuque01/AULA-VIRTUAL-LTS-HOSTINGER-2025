@php
    use Illuminate\Support\Facades\Route;

    $currentLocale = request()->route('locale') ?? app()->getLocale();
    $alternateLocale = $currentLocale === 'es' ? 'en' : 'es';
    $routeParameters = request()->route()?->parameters() ?? [];
    $alternateRoute = route(Route::currentRouteName(), array_merge($routeParameters, ['locale' => $alternateLocale]));

    $roleMessages = [
        'admin' => __('auth.roles.admin'),
        'teacher_admin' => __('auth.roles.teacher_admin'),
        'teacher' => __('auth.roles.teacher'),
        'student' => __('auth.roles.student'),
    ];
    $roleLabels = [
        'admin' => __('auth.role_labels.admin'),
        'teacher_admin' => __('auth.role_labels.teacher_admin'),
        'teacher' => __('auth.role_labels.teacher'),
        'student' => __('auth.role_labels.student'),
    ];
@endphp

<x-guest-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('auth.language_label') }}</p>
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="inline-flex rounded-full bg-slate-900/10 px-3 py-1 font-semibold text-slate-700">
                    {{ strtoupper($currentLocale) }}
                </span>
                @php
                    $switchKey = $alternateLocale === 'es' ? 'auth.switch_to_es' : 'auth.switch_to_en';
                @endphp
                <a href="{{ $alternateRoute }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-500 hover:border-slate-400 hover:text-slate-700">
                    {{ __($switchKey) }}
                </a>
            </div>
        </div>

        @if(!empty($targetRole) && isset($roleMessages[$targetRole]))
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 max-w-md">
                <p class="font-semibold">{{ __('auth.mode', ['role' => $roleLabels[$targetRole] ?? ucfirst($targetRole)]) }}</p>
                <p class="mt-1 text-indigo-800">{{ $roleMessages[$targetRole] }}</p>
            </div>
        @endif
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
        $hasGoogle = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    @endphp

    @if($hasGoogle)
        <div class="mb-6">
            <a href="{{ route('google.redirect', ['locale' => $currentLocale]) }}"
               class="flex items-center justify-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <x-icons.google class="h-5 w-5"/>
                {{ __('auth.continue_with_google') }}
            </a>
        </div>
    @endif

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
                <span class="ms-2 text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('auth.forgot_password') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('auth.login') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
