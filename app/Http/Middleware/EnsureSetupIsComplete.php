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
        if ($request->is('setup*') || $this->shouldBypass($request)) {
            return $next($request);
        }

        if (SetupState::isCompleted()) {
            return $next($request);
        }

        if (User::query()->count() === 0 || ! SetupState::isCompleted()) {
            return redirect()->to('/setup');
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
}
