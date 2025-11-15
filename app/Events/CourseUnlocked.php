<?php

namespace App\Events;

use App\Models\Course;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CourseUnlocked
{
    use Dispatchable;
    use SerializesModels;

    public Collection $recipients;

    public function __construct(
        public Course $course,
        iterable $recipients,
        public string $courseTitle,
        public string $courseSummary,
        public string $audienceLabel,
        public ?string $courseUrl = null,
        public ?string $intro = null,
    ) {
        $this->recipients = collect($recipients);
    }
}
