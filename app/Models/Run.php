<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
    protected $guarded = [];

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
