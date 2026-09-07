<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseVersion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'constraints' => 'array',
            'visible_examples' => 'array',
            'supported_languages' => 'array',
            'starter_code_by_language' => 'array',
            'function_signature_by_language' => 'array',
            'hints' => 'array',
            'official_solutions_by_language' => 'array',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function testBundle()
    {
        return $this->hasOne(TestBundle::class);
    }

    public function visibleTestCases()
    {
        return $this->hasManyThrough(TestCase::class, TestBundle::class)->where('visibility', 'VISIBLE')->orderBy('sort_order');
    }

    public function starterCodes()
    {
        return $this->hasMany(StarterCode::class);
    }

    public function hintRecords()
    {
        return $this->hasMany(Hint::class);
    }

    public function officialSolutions()
    {
        return $this->hasMany(OfficialSolution::class);
    }

    public function starterCodeFor(string $language): string
    {
        return $this->starter_code_by_language[$language] ?? '';
    }

    public function officialSolutionFor(string $language): ?string
    {
        return $this->official_solutions_by_language[$language] ?? null;
    }
}
