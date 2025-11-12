<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['slug','level','published'];

    public function i18n()
    {
        return $this->hasMany(CourseI18n::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable')->withTimestamps();
    }
}
