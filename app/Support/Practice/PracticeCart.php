<?php

namespace App\Support\Practice;

use App\Models\PracticePackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class PracticeCart
{
    private const SESSION_KEY = 'practice_cart';

    public static function ids(): array
    {
        return array_values(array_unique(array_filter(
            Session::get(self::SESSION_KEY, []),
            static fn ($id) => is_numeric($id)
        )));
    }

    public static function packages(): Collection
    {
        $ids = self::ids();

        if (empty($ids)) {
            return collect();
        }

        $order = array_flip($ids);

        return PracticePackage::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (PracticePackage $package) => $order[$package->id] ?? PHP_INT_MAX)
            ->values();
    }

    public static function add(int $packageId): void
    {
        $ids = self::ids();

        if (! in_array($packageId, $ids, true)) {
            $ids[] = $packageId;
        }

        Session::put(self::SESSION_KEY, $ids);
    }

    public static function remove(int $packageId): void
    {
        $filtered = array_values(array_filter(
            self::ids(),
            fn ($id) => (int) $id !== $packageId
        ));

        Session::put(self::SESSION_KEY, $filtered);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    public static function subtotal(): float
    {
        return self::packages()->sum('price_amount');
    }
}


