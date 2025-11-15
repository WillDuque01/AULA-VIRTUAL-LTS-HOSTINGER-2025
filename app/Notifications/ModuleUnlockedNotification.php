<?php

namespace App\Notifications;

use App\Models\Course;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(
        private readonly Course $course,
        private readonly string $moduleTitle,
        private readonly string $audienceLabel,
        private readonly ?string $moduleUrl = null,
        private readonly ?string $intro = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $courseTitle = method_exists($this->course, 'getAttribute') && $this->course->getAttribute('title')
            ? $this->course->getAttribute('title')
            : $this->course->slug;

        return $this->renderMail(
            'emails.templates.module-unlocked',
            [
                'notifiable' => $notifiable,
                'course' => $this->course,
                'moduleTitle' => $this->moduleTitle,
                'courseTitle' => $courseTitle,
                'audienceLabel' => $this->audienceLabel,
                'moduleUrl' => $this->moduleUrl ?? url('/courses/'.$this->course->slug.'#module'),
                'ctaLabel' => 'email.blocks.course.start_learning',
                'intro' => $this->intro ?? __('email.body.module_unlocked.intro', ['course' => $courseTitle]),
            ],
            'email.subjects.module_unlocked',
            ['course' => $courseTitle]
        );
    }
}
