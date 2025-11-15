<?php

namespace App\Notifications;

use App\Models\Course;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(
        private readonly Course $course,
        private readonly string $courseTitle,
        private readonly string $courseSummary,
        private readonly string $audienceLabel,
        private readonly ?string $courseUrl = null,
        private readonly ?string $intro = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->renderMail(
            'emails.templates.course-unlocked',
            [
                'notifiable' => $notifiable,
                'course' => $this->course,
                'courseTitle' => $this->courseTitle,
                'courseSummary' => $this->courseSummary,
                'audienceLabel' => $this->audienceLabel,
                'courseUrl' => $this->courseUrl ?? url('/courses/'.$this->course->slug),
                'ctaLabel' => 'email.blocks.course.start_learning',
                'intro' => $this->intro ?? __('email.body.course_unlocked.intro'),
            ],
            'email.subjects.course_unlocked',
            ['course' => $this->courseTitle]
        );
    }
}
