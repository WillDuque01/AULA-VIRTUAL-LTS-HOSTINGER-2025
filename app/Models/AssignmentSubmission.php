<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'body',
        'attachment_url',
        'status',
        'score',
        'max_points',
        'feedback',
        'rubric_scores',
        'submitted_at',
        'graded_at',
        'approved_at',
    ];

    protected $casts = [
        'rubric_scores' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


