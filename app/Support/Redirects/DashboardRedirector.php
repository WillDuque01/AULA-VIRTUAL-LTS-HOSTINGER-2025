<?php

declare(strict_types=1);

namespace App\Support\Redirects;

use App\Models\User;

class DashboardRedirector
{
    /**
     * Map of preferred target roles to the routes and role aliases we expect.
     *
     * @var array<string, array{roles: array<int, string>, route: string}>
     */
    private const ROLE_ROUTE_MAP = [
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

    public static function resolve(?User $user, ?string $locale = null, ?string $targetRole = null): string
    {
        $locale = $locale ?: config('app.locale', 'es');
        $roleKey = $targetRole ? strtolower($targetRole) : null;

        if ($roleKey && isset(self::ROLE_ROUTE_MAP[$roleKey])) {
            $entry = self::ROLE_ROUTE_MAP[$roleKey];

            if ($user?->hasAnyRole($entry['roles'])) {
                return route($entry['route'], ['locale' => $locale], false);
            }
        }

        foreach (self::ROLE_ROUTE_MAP as $entry) {
            if ($user?->hasAnyRole($entry['roles'])) {
                return route($entry['route'], ['locale' => $locale], false);
            }
        }

        return route('dashboard', ['locale' => $locale], false);
    }
}

