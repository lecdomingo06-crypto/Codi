<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    public const DIFFICULTIES = ['EASY', 'MEDIUM', 'HARD'];

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

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function concepts()
    {
        return $this->belongsToMany(Concept::class, 'exercise_concepts');
    }

    public function versions()
    {
        return $this->hasMany(ExerciseVersion::class);
    }

    public function activeVersion()
    {
        return $this->belongsTo(ExerciseVersion::class, 'active_version_id');
    }

    public function latestVersion()
    {
        return $this->hasOne(ExerciseVersion::class)->latestOfMany('version_number');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
