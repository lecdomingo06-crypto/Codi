<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Concept;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserStreak;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
                'timezone' => 'Asia/Manila',
            ],
        );

        $learner = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Learner',
                'password' => 'password',
                'role' => 'USER',
                'status' => 'ACTIVE',
                'timezone' => 'Asia/Manila',
            ],
        );

        UserStreak::firstOrCreate(['user_id' => $admin->id]);
        UserStreak::firstOrCreate(['user_id' => $learner->id]);

        $functions = Concept::firstOrCreate(
            ['slug' => 'functions'],
            ['name' => 'Functions', 'description' => 'Reusable blocks that accept inputs and return outputs.'],
        );

        $arithmetic = Concept::firstOrCreate(
            ['slug' => 'arithmetic'],
            ['name' => 'Arithmetic', 'description' => 'Basic operations on numeric values.'],
        );

        $course = Course::updateOrCreate(
            ['slug' => 'programming-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'Programming Fundamentals',
                'summary' => 'A compact path from functions to first coding challenges.',
                'description_markdown' => 'Learn small concepts, practice with guidance, then solve independently.',
                'publication_status' => 'PUBLISHED',
                'published_at' => now(),
            ],
        );

        if (! $course->versions()->exists()) {
            $course->versions()->create([
                'version_number' => 1,
                'title' => $course->title,
                'summary' => $course->summary,
                'description_markdown' => $course->description_markdown,
                'status' => 'PUBLISHED',
                'published_at' => now(),
            ]);
        }

        $lesson = Lesson::updateOrCreate(
            ['slug' => 'writing-small-functions'],
            [
                'course_id' => $course->id,
                'title' => 'Writing Small Functions',
                'summary' => 'Return values from a named function.',
                'body_markdown' => "A function names a repeatable idea. Inputs arrive as parameters, and `return` sends the result back to the caller.\n\nFor arithmetic exercises, focus on transforming the inputs directly before adding extra control flow.",
                'code_example' => "def add(a, b):\n    return a + b",
                'sort_order' => 1,
                'publication_status' => 'PUBLISHED',
                'published_at' => now(),
            ],
        );

        if (! $lesson->versions()->exists()) {
            $lesson->versions()->create([
                'version_number' => 1,
                'title' => $lesson->title,
                'summary' => $lesson->summary,
                'body_markdown' => $lesson->body_markdown,
                'code_example' => $lesson->code_example,
                'status' => 'PUBLISHED',
                'published_at' => now(),
            ]);
        }

        $exercise = Exercise::updateOrCreate(
            ['slug' => 'sum-two-numbers'],
            [
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'created_by' => $admin->id,
                'title' => 'Sum Two Numbers',
                'summary' => 'Return the sum of two integers.',
                'difficulty' => 'EASY',
                'publication_status' => 'PUBLISHED',
                'published_at' => now(),
            ],
        );

        $exercise->concepts()->sync([$functions->id, $arithmetic->id]);

        if (! $exercise->versions()->exists()) {
            $version = $exercise->versions()->create([
                'created_by' => $admin->id,
                'version_number' => 1,
                'title' => 'Sum Two Numbers',
                'summary' => 'Return the sum of two integers.',
                'description_markdown' => 'Write a function named `add` that accepts two integers and returns their sum.',
                'difficulty' => 'EASY',
                'constraints' => ['-10,000 <= a, b <= 10,000', 'Return a number, not a string.'],
                'visible_examples' => [['input' => ['a' => 2, 'b' => 3], 'output' => 5]],
                'supported_languages' => ['python', 'javascript', 'typescript'],
                'starter_code_by_language' => [
                    'python' => "def add(a, b):\n    # write your code here\n    pass",
                    'javascript' => "function add(a, b) {\n  // write your code here\n}",
                    'typescript' => "function add(a: number, b: number): number {\n  // write your code here\n  return 0;\n}",
                ],
                'function_signature_by_language' => [
                    'python' => 'def add(a, b):',
                    'javascript' => 'function add(a, b)',
                    'typescript' => 'function add(a: number, b: number): number',
                ],
                'hints' => [
                    ['order' => 1, 'content_markdown' => 'Use the addition operator on the two parameters.'],
                    ['order' => 2, 'content_markdown' => 'The returned expression can be a single line.'],
                ],
                'official_solutions_by_language' => [
                    'python' => "def add(a, b):\n    return a + b",
                    'javascript' => "function add(a, b) {\n  return a + b;\n}",
                    'typescript' => "function add(a: number, b: number): number {\n  return a + b;\n}",
                ],
                'explanation_markdown' => 'The function returns the arithmetic sum directly, so each test checks a different pair of input values.',
                'time_limit_ms' => 1000,
                'memory_limit_mb' => 128,
                'points' => 10,
                'status' => 'PUBLISHED',
                'published_at' => now(),
            ]);

            foreach ($version->starter_code_by_language as $language => $code) {
                $version->starterCodes()->create(['language' => $language, 'code' => $code]);
            }

            foreach ($version->hints as $hint) {
                $version->hintRecords()->create(['hint_order' => $hint['order'], 'content_markdown' => $hint['content_markdown']]);
            }

            foreach ($version->official_solutions_by_language as $language => $code) {
                $version->officialSolutions()->create(['language' => $language, 'code' => $code, 'explanation_markdown' => $version->explanation_markdown]);
            }

            $bundle = $version->testBundle()->create([
                'version_label' => 'v1',
                'status' => 'PUBLISHED',
                'checksum' => Str::uuid()->toString(),
            ]);

            $bundle->testCases()->createMany([
                ['visibility' => 'VISIBLE', 'name' => 'sample positive integers', 'input' => '{"a":2,"b":3}', 'expected_output' => '5', 'sort_order' => 1],
                ['visibility' => 'VISIBLE', 'name' => 'sample includes zero', 'input' => '{"a":0,"b":8}', 'expected_output' => '8', 'sort_order' => 2],
                ['visibility' => 'HIDDEN', 'name' => 'hidden negative sum', 'input' => '{"a":10,"b":-4}', 'expected_output' => '6', 'sort_order' => 1],
                ['visibility' => 'HIDDEN', 'name' => 'hidden lower bound', 'input' => '{"a":-10000,"b":1}', 'expected_output' => '-9999', 'sort_order' => 2],
            ]);

            $exercise->update(['active_version_id' => $version->id]);
        }

        Achievement::firstOrCreate(
            ['slug' => 'first-accepted-answer'],
            ['name' => 'First Accepted Answer', 'description' => 'Earned after your first accepted coding submission.', 'points' => 5],
        );
    }
}
