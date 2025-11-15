<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'target',
        'payload',
        'status',
        'attempts',
        'last_error',
        'last_attempt_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
