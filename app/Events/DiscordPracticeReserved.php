<?php

namespace App\Events;

use App\Models\DiscordPractice;
use App\Models\DiscordPracticeReservation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscordPracticeReserved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public DiscordPractice $practice,
        public DiscordPracticeReservation $reservation
    ) {
    }
}


