<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Redirects\DashboardRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, ?string $targetRole = null): View
    {
        $role = $targetRole ?? $request->string('target_role')->toString();

        return view('auth.login', [
            'targetRole' => $role,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(
            $this->resolveDashboardRedirect($request)
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function resolveDashboardRedirect(Request $request): string
    {
        return DashboardRedirector::resolve(
            $request->user(),
            $request->route('locale') ?? app()->getLocale(),
            $request->string('target_role')->lower()->value()
        );
    }
}
