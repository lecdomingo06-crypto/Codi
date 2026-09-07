<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestBundle extends Model
{
    protected $guarded = [];

    public function testCases()
    {
        return $this->hasMany(TestCase::class)->orderBy('sort_order');
    }

    public function visibleTestCases()
    {
        return $this->hasMany(TestCase::class)->where('visibility', 'VISIBLE')->orderBy('sort_order');
    }

    public function hiddenTestCases()
    {
        return $this->hasMany(TestCase::class)->where('visibility', 'HIDDEN')->orderBy('sort_order');
    }
}
