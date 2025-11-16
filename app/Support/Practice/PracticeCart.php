<?php

namespace App\Support\Practice;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class PracticeCart
{
    private const SESSION_KEY = 'commerce_cart';

    public static function ids(): array
    {
        return array_values(array_unique(array_filter(
            Session::get(self::SESSION_KEY, []),
            static fn ($id) => is_numeric($id)
        )));
    }

    public static function products(): Collection
    {
        $ids = self::ids();

        if (empty($ids)) {
            return collect();
        }

        $order = array_flip($ids);

        return Product::query()
            ->whereIn('id', $ids)
            ->with('productable')
            ->get()
            ->sortBy(fn (Product $product) => $order[$product->id] ?? PHP_INT_MAX)
            ->values();
    }

    public static function add(int $productId): void
    {
        self::addProduct($productId);
    }

    public static function addProduct(int $productId): void
    {
        $ids = self::ids();

        if (! in_array($productId, $ids, true)) {
            $ids[] = $productId;
        }

        Session::put(self::SESSION_KEY, $ids);
    }

    public static function remove(int $productId): void
    {
        $filtered = array_values(array_filter(
            self::ids(),
            fn ($id) => (int) $id !== $productId
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
        return (float) self::products()->sum('price_amount');
    }
}

