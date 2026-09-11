<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Concept;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Module;
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
                'summary' => 'Start with core programming ideas, number systems, flowcharts, and beginner C++ syntax.',
                'category' => 'Programming',
                'duration_minutes' => 105,
                'difficulty' => 'EASY',
                'description_markdown' => 'Learn the foundations behind programming before moving into your first C++ programs.',
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

        $fundamentalsModules = collect([
            [
                'slug' => 'programming-foundations',
                'title' => 'Programming Foundations',
                'summary' => 'Understand what programming is and how computers represent information.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'planning-programs',
                'title' => 'Planning Programs',
                'summary' => 'Use flowcharts to plan program logic before writing code.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'cpp-fundamentals',
                'title' => 'C++ Fundamentals',
                'summary' => 'Write simple C++ programs with correct syntax, structure, and input.',
                'sort_order' => 3,
            ],
        ])->mapWithKeys(fn (array $module) => [
            $module['slug'] => Module::updateOrCreate(
                ['course_id' => $course->id, 'slug' => $module['slug']],
                [
                    'title' => $module['title'],
                    'summary' => $module['summary'],
                    'sort_order' => $module['sort_order'],
                ],
            ),
        ]);

        foreach ([
            [
                'slug' => 'patterns-for-problem-solvers',
                'title' => 'Patterns for Problem Solvers',
                'summary' => 'Turn arrays, strings, and graphs into a repeatable toolkit for solving challenges.',
                'category' => 'Data Structures & Algorithms',
                'duration_minutes' => 240,
                'difficulty' => 'MEDIUM',
            ],
            [
                'slug' => 'algorithms-in-motion',
                'title' => 'Algorithms in Motion',
                'summary' => 'Build intuition for recursion, searching, sorting, and choosing the right trade-off.',
                'category' => 'Data Structures & Algorithms',
                'duration_minutes' => 300,
                'difficulty' => 'HARD',
            ],
            [
                'slug' => 'python-for-problem-solvers',
                'title' => 'Python for Problem Solvers',
                'summary' => 'Write clear Python while learning the language features that make solutions concise.',
                'category' => 'Python',
                'duration_minutes' => 180,
                'difficulty' => 'EASY',
            ],
            [
                'slug' => 'web-foundations',
                'title' => 'Web Foundations',
                'summary' => 'Understand how browsers, requests, and server-rendered pages work together.',
                'category' => 'Web Development',
                'duration_minutes' => 210,
                'difficulty' => 'EASY',
            ],
            [
                'slug' => 'designing-reliable-services',
                'title' => 'Designing Reliable Services',
                'summary' => 'Practice the decisions behind resilient APIs, storage, queues, and growing systems.',
                'category' => 'System Design',
                'duration_minutes' => 270,
                'difficulty' => 'HARD',
            ],
        ] as $catalogCourse) {
            $seededCourse = Course::updateOrCreate(
                ['slug' => $catalogCourse['slug']],
                $catalogCourse + [
                    'created_by' => $admin->id,
                    'description_markdown' => $catalogCourse['summary'],
                    'publication_status' => 'PUBLISHED',
                    'published_at' => now(),
                ],
            );

            if (! $seededCourse->versions()->exists()) {
                $seededCourse->versions()->create([
                    'version_number' => 1,
                    'title' => $seededCourse->title,
                    'summary' => $seededCourse->summary,
                    'description_markdown' => $seededCourse->description_markdown,
                    'status' => 'PUBLISHED',
                    'published_at' => now(),
                ]);
            }
        }

        Lesson::where('course_id', $course->id)
            ->where('slug', 'writing-small-functions')
            ->update([
                'publication_status' => 'ARCHIVED',
                'archived_at' => now(),
            ]);

        $programmingLessons = collect([
            [
                'module' => 'programming-foundations',
                'slug' => 'programming-concepts',
                'title' => 'Programming Concepts',
                'summary' => 'Learn what programming means and how instructions become working software.',
                'video_url' => 'https://youtu.be/a9qTpSvE1UY?si=VHCDVjSj0mk-A_vq',
                'duration_minutes' => 17,
                'sort_order' => 1,
                'body_markdown' => "Programming is the process of giving clear instructions to a computer.\n\nIn this lesson, focus on the basic ideas: input, process, output, variables, and step-by-step problem solving.",
                'code_example' => null,
            ],
            [
                'module' => 'programming-foundations',
                'slug' => 'number-systems',
                'title' => 'Number Systems',
                'summary' => 'Understand binary, decimal, and how computers represent values.',
                'video_url' => 'https://youtu.be/TmLS_uYoDs8?si=DsBa--LE4DZXYRq-',
                'duration_minutes' => 18,
                'sort_order' => 2,
                'body_markdown' => "Number systems explain how values are represented.\n\nComputers work with binary, while humans usually use decimal. Learning the conversion helps you understand memory, data, and low-level programming.",
                'code_example' => null,
            ],
            [
                'module' => 'programming-foundations',
                'slug' => 'programming-languages',
                'title' => 'Programming Languages',
                'summary' => 'Compare programming languages and why C++ is useful for fundamentals.',
                'video_url' => 'https://youtu.be/NG314e1_NZY?si=N0nOeq08lF0D5j8J',
                'duration_minutes' => 16,
                'sort_order' => 3,
                'body_markdown' => "Programming languages let humans write instructions that computers can execute.\n\nDifferent languages are designed for different goals, but they share core ideas like variables, control flow, functions, and data structures.",
                'code_example' => null,
            ],
            [
                'module' => 'planning-programs',
                'slug' => 'flowchart',
                'title' => 'Flowchart',
                'summary' => 'Plan logic visually before writing code.',
                'video_url' => 'https://youtu.be/GJK_CSGKdG0?si=AS4JKgDCnitMaC9D',
                'duration_minutes' => 18,
                'sort_order' => 1,
                'body_markdown' => "A flowchart is a visual plan for a program.\n\nUse it to map decisions, repeated steps, and outputs before you write code. This makes your solution easier to reason about.",
                'code_example' => null,
            ],
            [
                'module' => 'cpp-fundamentals',
                'slug' => 'cpp-basic-syntax-and-code-structure',
                'title' => 'C++ Basic Syntax and Code Structure',
                'summary' => 'Learn the shape of a simple C++ program.',
                'video_url' => 'https://youtu.be/y4XkyMjMHkg?si=pSA3kzE8liFf_Yb-',
                'duration_minutes' => 20,
                'sort_order' => 1,
                'body_markdown' => "C++ programs have a clear structure: include libraries, define `main`, write statements, and return a result.\n\nPay attention to semicolons, braces, and how code is grouped.",
                'code_example' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"Hello, Coddy!\";\n    return 0;\n}",
            ],
            [
                'module' => 'cpp-fundamentals',
                'slug' => 'cpp-input',
                'title' => 'C++ Input',
                'summary' => 'Read user input using C++ streams.',
                'video_url' => 'https://youtu.be/doQLHeyEmDo?si=cWgBSfztSK7pGjZF',
                'duration_minutes' => 16,
                'sort_order' => 2,
                'body_markdown' => "Input lets a program receive values from the user.\n\nIn C++, `cin` reads values and stores them in variables. This is the start of interactive programs.",
                'code_example' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    int age;\n    cin >> age;\n    cout << \"Age: \" << age;\n    return 0;\n}",
            ],
        ]);

        $lessonsBySlug = $programmingLessons->mapWithKeys(function (array $lessonData) use ($course, $fundamentalsModules) {
            $lesson = Lesson::updateOrCreate(
                ['slug' => $lessonData['slug']],
                [
                    'course_id' => $course->id,
                    'module_id' => $fundamentalsModules[$lessonData['module']]->id,
                    'title' => $lessonData['title'],
                    'summary' => $lessonData['summary'],
                    'video_url' => $lessonData['video_url'],
                    'duration_minutes' => $lessonData['duration_minutes'],
                    'body_markdown' => $lessonData['body_markdown'],
                    'code_example' => $lessonData['code_example'],
                    'sort_order' => $lessonData['sort_order'],
                    'publication_status' => 'PUBLISHED',
                    'published_at' => now(),
                    'archived_at' => null,
                ],
            );

            $lesson->versions()->updateOrCreate(
                ['version_number' => 1],
                [
                    'title' => $lesson->title,
                    'summary' => $lesson->summary,
                    'video_url' => $lesson->video_url,
                    'duration_minutes' => $lesson->duration_minutes,
                    'body_markdown' => $lesson->body_markdown,
                    'code_example' => $lesson->code_example,
                    'status' => 'PUBLISHED',
                    'published_at' => now(),
                ],
            );

            return [$lessonData['slug'] => $lesson];
        });

        $lesson = $lessonsBySlug['cpp-input'];

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
                'supported_languages' => ['python', 'javascript', 'typescript', 'php', 'cpp'],
                'starter_code_by_language' => [
                    'python' => "def add(a, b):\n    # write your code here\n    pass",
                    'javascript' => "function add(a, b) {\n  // write your code here\n}",
                    'typescript' => "function add(a: number, b: number): number {\n  // write your code here\n  return 0;\n}",
                    'php' => "function add(\$a, \$b) {\n    // write your code here\n    return 0;\n}",
                    'cpp' => "int add(int a, int b) {\n    // write your code here\n    return 0;\n}",
                ],
                'function_signature_by_language' => [
                    'python' => 'def add(a, b):',
                    'javascript' => 'function add(a, b)',
                    'typescript' => 'function add(a: number, b: number): number',
                    'php' => 'function add($a, $b)',
                    'cpp' => 'int add(int a, int b)',
                ],
                'hints' => [
                    ['order' => 1, 'content_markdown' => 'Use the addition operator on the two parameters.'],
                    ['order' => 2, 'content_markdown' => 'The returned expression can be a single line.'],
                ],
                'official_solutions_by_language' => [
                    'python' => "def add(a, b):\n    return a + b",
                    'javascript' => "function add(a, b) {\n  return a + b;\n}",
                    'typescript' => "function add(a: number, b: number): number {\n  return a + b;\n}",
                    'php' => "function add(\$a, \$b) {\n    return \$a + \$b;\n}",
                    'cpp' => "int add(int a, int b) {\n    return a + b;\n}",
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

        $version = $exercise->fresh('activeVersion')->activeVersion
            ?? $exercise->versions()->latest('version_number')->first();

        if ($version) {
            $additionalStarters = [
                'php' => "function add(\$a, \$b) {\n    // write your code here\n    return 0;\n}",
                'cpp' => "int add(int a, int b) {\n    // write your code here\n    return 0;\n}",
            ];
            $additionalSignatures = [
                'php' => 'function add($a, $b)',
                'cpp' => 'int add(int a, int b)',
            ];
            $additionalSolutions = [
                'php' => "function add(\$a, \$b) {\n    return \$a + \$b;\n}",
                'cpp' => "int add(int a, int b) {\n    return a + b;\n}",
            ];

            $version->forceFill([
                'supported_languages' => array_values(array_unique(array_merge($version->supported_languages ?? [], array_keys($additionalStarters)))),
                'starter_code_by_language' => array_merge($version->starter_code_by_language ?? [], $additionalStarters),
                'function_signature_by_language' => array_merge($version->function_signature_by_language ?? [], $additionalSignatures),
                'official_solutions_by_language' => array_merge($version->official_solutions_by_language ?? [], $additionalSolutions),
            ])->save();

            foreach ($additionalStarters as $language => $code) {
                $version->starterCodes()->updateOrCreate(['language' => $language], ['code' => $code]);
            }

            foreach ($additionalSolutions as $language => $code) {
                $version->officialSolutions()->updateOrCreate(
                    ['language' => $language],
                    ['code' => $code, 'explanation_markdown' => $version->explanation_markdown],
                );
            }
        }

        Achievement::firstOrCreate(
            ['slug' => 'first-accepted-answer'],
            ['name' => 'First Accepted Answer', 'description' => 'Earned after your first accepted coding submission.', 'points' => 5],
        );
    }
}
