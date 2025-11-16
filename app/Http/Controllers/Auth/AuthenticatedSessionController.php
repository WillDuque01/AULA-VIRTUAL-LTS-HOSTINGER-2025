<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
        $user = $request->user();
        $targetRole = $request->string('target_role')->lower()->value();
        $locale = $request->route('locale') ?? app()->getLocale();

        $map = [
            'admin' => [
                'roles' => ['Admin'],
                'route' => 'dashboard.admin',
            ],
            'teacher_admin' => [
                'roles' => ['teacher_admin', 'Profesor'],
                'route' => 'dashboard',
            ],
            'teacher' => [
                'roles' => ['teacher'],
                'route' => 'dashboard.teacher',
            ],
            'student' => [
                'roles' => ['student_free', 'student_paid', 'student_vip'],
                'route' => 'dashboard.student',
            ],
        ];

        if ($targetRole && isset($map[$targetRole]) && $user?->hasAnyRole($map[$targetRole]['roles'])) {
            return route($map[$targetRole]['route'], ['locale' => $locale], false);
        }

        foreach ($map as $entry) {
            if ($user?->hasAnyRole($entry['roles'])) {
                return route($entry['route'], ['locale' => $locale], false);
            }
        }

        return route('dashboard', ['locale' => $locale], false);
    }
}
