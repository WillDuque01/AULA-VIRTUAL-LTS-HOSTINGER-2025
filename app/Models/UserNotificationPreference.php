<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $table = 'user_notification_prefs';

    protected $fillable = [
        'user_id',
        'system',
        'course',
        'reminders',
        'marketing',
    ];

    protected $casts = [
        'system' => 'boolean',
        'course' => 'boolean',
        'reminders' => 'boolean',
        'marketing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
