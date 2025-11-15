<?php

namespace App\Events;

use App\Models\Course;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ModuleUnlocked
{
    use Dispatchable;
    use SerializesModels;

    public Collection $recipients;

    public function __construct(
        public Course $course,
        iterable $recipients,
        public string $moduleTitle,
        public string $audienceLabel,
        public ?string $moduleUrl = null,
        public ?string $intro = null,
    ) {
        $this->recipients = collect($recipients);
    }
}
