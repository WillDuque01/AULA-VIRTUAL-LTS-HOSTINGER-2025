<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoPlayerEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_id',
        'event',
        'provider',
        'playback_seconds',
        'watched_seconds',
        'video_duration',
        'playback_rate',
        'context_tag',
        'metadata',
        'recorded_at',
        'synced_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'synced_at' => 'datetime',
        'playback_seconds' => 'integer',
        'watched_seconds' => 'integer',
        'video_duration' => 'integer',
        'playback_rate' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

