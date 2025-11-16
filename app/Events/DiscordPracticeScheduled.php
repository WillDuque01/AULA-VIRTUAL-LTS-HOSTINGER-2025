<?php

namespace App\Events;

use App\Models\DiscordPractice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscordPracticeScheduled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public DiscordPractice $practice)
    {
    }
}


