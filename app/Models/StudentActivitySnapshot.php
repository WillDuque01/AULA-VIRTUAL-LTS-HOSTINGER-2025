<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivitySnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'practice_package_id',
        'category',
        'scope',
        'value',
        'payload',
        'captured_at',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'captured_at' => 'datetime',
        'synced_at' => 'datetime',
        'value' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function practicePackage(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class);
    }
}

