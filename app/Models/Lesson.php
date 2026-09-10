<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function versions()
    {
        return $this->hasMany(LessonVersion::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }
}
