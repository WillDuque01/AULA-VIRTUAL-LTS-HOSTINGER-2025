<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'instructions',
        'due_at',
        'max_points',
        'passing_score',
        'requires_approval',
        'rubric',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'passing_score' => 'integer',
        'requires_approval' => 'boolean',
        'rubric' => 'array',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}


