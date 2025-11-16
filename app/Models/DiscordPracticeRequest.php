<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordPracticeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'discord_practice_id',
        'user_id',
        'cohort_label',
        'status',
        'notes',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function practice(): BelongsTo
    {
        return $this->belongsTo(DiscordPractice::class, 'discord_practice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}


