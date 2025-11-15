<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'sender_id' => User::factory(),
            'type' => 'direct',
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'locale' => $this->faker->randomElement(['es', 'en']),
            'notify_email' => true,
            'metadata' => ['via' => 'factory'],
            'sent_at' => now(),
        ];
    }
}
