<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageRevision>
 */
class PageRevisionFactory extends Factory
{
    protected $model = PageRevision::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'label' => $this->faker->sentence(2),
            'layout' => [
                [
                    'type' => 'hero',
                    'props' => [
                        'headline' => $this->faker->sentence(),
                        'subheadline' => $this->faker->sentence(6),
                        'cta_label' => 'Comenzar',
                        'cta_url' => '#',
                    ],
                ],
            ],
            'settings' => [
                'theme' => 'light',
            ],
            'author_id' => null,
        ];
    }
}


