<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function exerciseVersion()
    {
        return $this->belongsTo(ExerciseVersion::class);
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
