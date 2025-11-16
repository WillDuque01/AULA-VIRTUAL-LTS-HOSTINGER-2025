<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherSubmissionHistory;

class TeacherSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'chapter_id',
        'type',
        'title',
        'summary',
        'payload',
        'status',
        'feedback',
        'approved_by',
        'approved_at',
        'result_type',
        'result_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $submission): void {
            $submission->history()->create([
                'status' => $submission->status,
                'changed_by' => $submission->user_id,
                'notes' => __('Propuesta enviada por el docente'),
                'metadata' => [
                    'type' => $submission->type,
                    'title' => $submission->title,
                ],
            ]);
        });

        static::updated(function (self $submission): void {
            if (! $submission->wasChanged('status')) {
                return;
            }

            $submission->history()->create([
                'status' => $submission->status,
                'changed_by' => $submission->approved_by ?? Auth::id(),
                'notes' => $submission->feedback,
                'metadata' => [
                    'result_type' => $submission->result_type,
                    'result_id' => $submission->result_id,
                ],
            ]);
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function result(): MorphTo
    {
        return $this->morphTo(null, 'result_type', 'result_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TeacherSubmissionHistory::class)->latest();
    }
}

