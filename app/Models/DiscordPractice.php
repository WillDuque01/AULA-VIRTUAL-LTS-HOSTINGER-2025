<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscordPractice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'type',
        'title',
        'description',
        'cohort_label',
        'practice_package_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'capacity',
        'discord_channel_url',
        'meeting_token',
        'status',
        'created_by',
        'requires_package',
        'attendance_synced_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'capacity' => 'integer',
        'requires_package' => 'boolean',
        'attendance_synced_at' => 'datetime',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class, 'practice_package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(DiscordPracticeReservation::class);
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(DiscordPracticeRequest::class)->where('status', 'pending');
    }
}


