<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoHeatmapSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'bucket',
        'reach_count',
    ];

    protected $casts = [
        'bucket' => 'integer',
        'reach_count' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

