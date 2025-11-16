<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetrySyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'processed',
        'driver_count',
        'duration_ms',
        'message',
        'triggered_by',
    ];

    protected $casts = [
        'processed' => 'integer',
        'driver_count' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}


