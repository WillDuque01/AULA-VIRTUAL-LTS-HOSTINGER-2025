<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body_markdown',
        'translations',
    ];

    protected $casts = [
        'translations' => 'array',
    ];

    public function campaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }
}
