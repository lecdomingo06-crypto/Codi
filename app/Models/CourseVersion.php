<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseVersion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
