<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubmissionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_submission_id',
        'status',
        'changed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(TeacherSubmission::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}


