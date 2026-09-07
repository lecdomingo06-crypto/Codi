<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyExerciseActivity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answer_count' => 'integer',
            'accepted_answer_count' => 'integer',
            'first_answer_at' => 'datetime',
            'last_answer_at' => 'datetime',
        ];
    }
}
