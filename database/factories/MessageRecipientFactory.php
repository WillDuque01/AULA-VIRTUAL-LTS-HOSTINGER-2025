<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageRecipientFactory extends Factory
{
    protected $model = MessageRecipient::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'user_id' => User::factory(),
            'status' => 'unread',
            'metadata' => ['via' => 'factory'],
        ];
    }
}
