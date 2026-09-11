<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
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
            'duration_minutes' => 'integer',
        ];
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    public function versions()
    {
        return $this->hasMany(CourseVersion::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
