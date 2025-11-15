<?php

namespace App\Http\Middleware;

use App\Models\SetupState;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureSetupIsComplete
{
    protected static bool $forceForConsole = false;

    public function handle(Request $request, Closure $next)
    {
        if ($this->isSetupRoute($request) || $this->shouldBypass($request)) {
            return $next($request);
        }

        if (SetupState::isCompleted()) {
            return $next($request);
        }

        if (User::query()->count() === 0 || ! SetupState::isCompleted()) {
            $locale = $request->route('locale') ?: $request->segment(1);
            if (! in_array($locale, config('app.available_locales', ['es', 'en']), true)) {
                $locale = config('app.locale', 'es');
            }

            return redirect()->to('/'.$locale.'/setup');
        }

        return $next($request);
    }

    private function shouldBypass(Request $request): bool
    {
        if ($request->header('X-Livewire') || $request->is('livewire/*')) {
            return true;
        }

        if (app()->runningInConsole() && ! static::$forceForConsole) {
            return true;
        }

        return app()->runningUnitTests() && ! static::$forceForConsole;
    }

    public static function forceForConsole(bool $state = true): void
    {
        static::$forceForConsole = $state;
    }

    private function isSetupRoute(Request $request): bool
    {
        return $request->is('setup*') || $request->is('*/setup*');
    }
}
