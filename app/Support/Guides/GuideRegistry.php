<?php

namespace App\Support\Guides;

use App\Models\User;
use Illuminate\Support\Arr;

class GuideRegistry
{
    public static function integrationGuides(): array
    {
        return config('integration_guides.categories', []);
    }

    public static function context(string $key): array
    {
        return config("experience_guides.contexts.$key", []);
    }

    public static function contextCards(string $key): array
    {
        return self::context($key)['cards'] ?? [];
    }

    public static function routeGuides(?string $routeName, ?string $audience = null): array
    {
        if (! $routeName) {
            return [];
        }

        $guide = config("experience_guides.routes.$routeName");

        if (! $guide) {
            return [];
        }

        $audiences = Arr::get($guide, 'audiences', []);

        if (! empty($audiences) && $audience !== null && ! in_array($audience, (array) $audiences, true)) {
            return [];
        }

        $cards = Arr::get($guide, 'cards', []);

        return collect($cards)
            ->filter(function ($card) use ($audience) {
                $cardAudiences = Arr::get($card, 'audiences', []);

                if (empty($cardAudiences) || $audience === null) {
                    return true;
                }

                return in_array($audience, (array) $cardAudiences, true);
            })
            ->values()
            ->all();
    }

    public static function audienceFromUser(?User $user): string
    {
        if (! $user) {
            return 'guest';
        }

        if (
            $user->hasRole('Admin')
            || $user->hasRole('admin')
            || $user->can('manage-settings')
        ) {
            return 'admin';
        }

        if ($user->hasRole('teacher_admin') || $user->hasRole('Profesor')) {
            return 'teacher';
        }

        return 'student';
    }
}


