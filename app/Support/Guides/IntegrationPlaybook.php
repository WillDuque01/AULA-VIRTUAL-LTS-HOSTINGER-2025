<?php

namespace App\Support\Guides;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IntegrationPlaybook
{
    /**
     * Obtiene el playbook agrupado por categoría filtrado por audiencia.
     *
     * @return array<int,array{key:string,title:string,items:array<int,array>}>
     */
    public static function grouped(string $audience = 'admin'): array
    {
        $categories = config('integration_guides.categories', []);

        return collect($categories)
            ->map(function (array $category) use ($audience) {
                $items = collect($category['providers'] ?? [])
                    ->map(fn (array $provider) => self::normalizeProvider($provider, $category))
                    ->filter(fn (?array $item) => $item !== null && self::forAudience($item, $audience))
                    ->map(fn (array $item) => self::evaluate($item))
                    ->values();

                if ($items->isEmpty()) {
                    return null;
                }

                return [
                    'key' => $category['key'] ?? Str::slug($category['title'] ?? 'integraciones'),
                    'title' => $category['title'] ?? 'Integraciones',
                    'items' => $items->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Verifica el estado de un bloque del playbook.
     */
    protected static function evaluate(array $item): array
    {
        $missing = collect($item['env'])
            ->reject(function (string $envKey) {
                $value = env($envKey);

                if (is_bool($value)) {
                    return $value === true;
                }

                return filled($value) && ! in_array($value, ['false', '0'], true);
            })
            ->values()
            ->all();

        $ok = empty($missing);

        return array_merge($item, [
            'ok' => $ok,
            'status' => $ok ? __('Completado') : __('Pendiente'),
            'missing' => $missing,
        ]);
    }

    protected static function forAudience(array $item, string $audience): bool
    {
        $audiences = Arr::wrap($item['audiences'] ?? ['admin']);

        return in_array($audience, $audiences, true);
    }

    protected static function normalizeProvider(array $provider, array $category): ?array
    {
        if (empty($provider['playbook'])) {
            return null;
        }

        $playbook = $provider['playbook'];

        return [
            'category' => $category['title'] ?? 'Integraciones',
            'label' => $provider['name'] ?? 'Integración',
            'docs' => $provider['docs'] ?? null,
            'tokens' => $provider['tokens'] ?? [],
            'audiences' => Arr::wrap($playbook['audiences'] ?? ['admin']),
            'env' => Arr::wrap($playbook['env'] ?? []),
            'status_hint' => $playbook['status_hint'] ?? null,
            'next_steps' => $playbook['next_steps'] ?? [],
        ];
    }
}


