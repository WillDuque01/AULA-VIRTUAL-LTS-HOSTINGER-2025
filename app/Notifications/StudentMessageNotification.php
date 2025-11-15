<?php

namespace App\Notifications;

use App\Models\Message;
use App\Notifications\Concerns\RendersMailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class StudentMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RendersMailTemplate;

    public function __construct(private readonly Message $message)
    {
        $this->message->loadMissing('sender');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->message->sender?->name ?? __('Student');
        $locale = app()->getLocale();
        $messageUrl = route('student.messages', ['locale' => $locale]) . '?message=' . $this->message->uuid;

        return $this->renderMail(
            'emails.templates.message-notification',
            [
                'notifiable' => $notifiable,
                'message' => $this->message,
                'messagePreview' => Str::limit(strip_tags($this->message->body), 320),
                'messageUrl' => $messageUrl,
                'intro' => __('email.body.message.student_intro_teacher', ['name' => $studentName]),
            ],
            'email.subjects.student_message',
            ['name' => $studentName]
        );
    }
}
