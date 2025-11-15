<?php

namespace App\Support\Messaging;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Tier;
use App\Models\User;
use App\Notifications\StudentMessageNotification;
use App\Notifications\TeacherMessageNotification;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class MessageService
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    /**
     * @param  iterable<int, User>|Arrayable<int, User>|Collection<int, User>  $recipients
     */
    public function send(User $sender, iterable $recipients, array $attributes): Message
    {
        $collection = $this->normalizeRecipients($recipients);

        if ($collection->isEmpty()) {
            throw new InvalidArgumentException('Recipients collection cannot be empty.');
        }

        return $this->db->transaction(function () use ($sender, $collection, $attributes) {
            $message = Message::create([
                'sender_id' => $sender->id,
                'parent_id' => $attributes['parent_id'] ?? null,
                'type' => $attributes['type'] ?? 'direct',
                'subject' => $attributes['subject'] ?? null,
                'body' => $attributes['body'],
                'locale' => $attributes['locale'] ?? app()->getLocale(),
                'notify_email' => $attributes['notify_email'] ?? true,
                'metadata' => $attributes['metadata'] ?? null,
                'sent_at' => now(),
            ]);

            $collection->each(function (User $recipient) use ($message) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'user_id' => $recipient->id,
                    'status' => 'unread',
                ]);
            });

            if ($message->shouldNotifyByEmail()) {
                $collection->each(function (User $recipient) use ($message, $sender) {
                    $notification = $this->resolveNotification($sender, $recipient, $message);

                    if ($notification) {
                        Notification::send($recipient, $notification);
                    }
                });
            }

            return $message->load(['sender', 'recipients.user']);
        });
    }

    protected function normalizeRecipients(iterable $recipients): Collection
    {
        if ($recipients instanceof Collection) {
            return $recipients;
        }

        if ($recipients instanceof Arrayable) {
            return collect($recipients->toArray());
        }

        return collect($recipients);
    }

    protected function resolveNotification(User $sender, User $recipient, Message $message): ?object
    {
        if ($sender->hasAnyRole(['teacher_admin', 'teacher'])) {
            return new TeacherMessageNotification($message);
        }

        if ($sender->hasRole('student_free') || $sender->hasRole('student_paid') || $sender->hasRole('student_vip')) {
            return new StudentMessageNotification($message);
        }

        if ($recipient->hasAnyRole(['teacher_admin', 'teacher'])) {
            return new StudentMessageNotification($message);
        }

        return new TeacherMessageNotification($message);
    }

    public function resolveTierRecipients(int $tierId): Collection
    {
        $tier = Tier::with('users')->findOrFail($tierId);

        return $tier->activeUsers()->get();
    }
}
