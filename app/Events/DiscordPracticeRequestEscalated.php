<?php

namespace App\Events;

use App\Models\Lesson;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscordPracticeRequestEscalated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Lesson $lesson,
        public int $pendingCount
    ) {
    }
}


