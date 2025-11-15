<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'sender_id',
        'parent_id',
        'type',
        'subject',
        'body',
        'locale',
        'notify_email',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'notify_email' => 'boolean',
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $message) {
            if (empty($message->uuid)) {
                $message->uuid = (string) Str::uuid();
            }

            if (empty($message->locale)) {
                $message->locale = app()->getLocale();
            }
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('sender_id', $user->id)
            ->orWhereHas('recipients', fn ($recipientQuery) => $recipientQuery->where('user_id', $user->id));
    }

    public function thread(): HasMany
    {
        return $this->replies()->with(['sender', 'recipients.user']);
    }

    public function route(): string
    {
        return url('/dashboard/messages/'.$this->uuid);
    }

    public function shouldNotifyByEmail(): bool
    {
        return $this->notify_email === true;
    }
}
