<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'type' => 'practice_pack',
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'excerpt' => $this->faker->sentence(12),
            'description' => $this->faker->paragraph(),
            'status' => 'published',
            'category' => 'packs',
            'price_amount' => 59,
            'price_currency' => 'USD',
            'compare_at_amount' => null,
            'is_featured' => false,
            'badge' => null,
            'thumbnail_path' => null,
            'inventory' => null,
            'meta' => [
                'sessions' => 4,
            ],
        ];
    }
}


