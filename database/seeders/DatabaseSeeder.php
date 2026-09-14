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
                'summary' => 'Build visual intuition for data structures, recursion, searching, sorting, graph traversal, and trade-offs.',
                'category' => 'Data Structures & Algorithms',
                'duration_minutes' => 300,
                'difficulty' => 'MEDIUM',
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

        $patternsCourse = Course::where('slug', 'patterns-for-problem-solvers')->firstOrFail();
        $patternsModules = collect([
            [
                'slug' => 'pattern-map',
                'title' => 'Pattern Map',
                'summary' => 'Learn how to recognize the shape of common coding challenges.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'linear-patterns',
                'title' => 'Linear Patterns',
                'summary' => 'Practice scans, counts, two pointers, and windows across arrays and strings.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'search-and-branching',
                'title' => 'Search and Branching',
                'summary' => 'Use sorted search, stack thinking, traversal, and branching choices.',
                'sort_order' => 3,
            ],
        ])->mapWithKeys(fn (array $module) => [
            $module['slug'] => Module::updateOrCreate(
                ['course_id' => $patternsCourse->id, 'slug' => $module['slug']],
                [
                    'title' => $module['title'],
                    'summary' => $module['summary'],
                    'sort_order' => $module['sort_order'],
                ],
            ),
        ]);

        $patternsLessons = collect([
            [
                'module' => 'pattern-map',
                'slug' => 'pps-dsa-patterns-overview',
                'title' => 'Data Structure and Algorithm Patterns',
                'summary' => 'Watch a full pattern walkthrough for arrays, strings, hash maps, two pointers, sliding window, search, BFS, DFS, backtracking, and heaps.',
                'video_url' => 'https://www.youtube.com/watch?v=Z_c4byLrNBU',
                'duration_minutes' => 75,
                'sort_order' => 1,
                'body_markdown' => "Use this lesson as the map for the whole course.\n\nThe important move is pattern recognition: notice the input shape, notice what repeats, then choose a tool. Arrays and strings often invite scans, two pointers, or windows. Hash maps help when you need counts or fast lookup. Graphs and trees need traversal.\n\nDo not try to memorize every problem. Try to name the shape of the problem before coding.",
                'code_example' => null,
            ],
            [
                'module' => 'pattern-map',
                'slug' => 'pps-choosing-a-pattern',
                'title' => 'Choosing a Pattern',
                'summary' => 'Turn problem clues into a repeatable decision process.',
                'video_url' => 'https://www.youtube.com/watch?v=UrcwDOEBzZE',
                'duration_minutes' => 25,
                'sort_order' => 2,
                'body_markdown' => "Most interview problems give clues in the wording.\n\nIf the prompt asks for pairs, compare from both ends or use a hash map. If it asks for longest or shortest contiguous section, think sliding window. If it says sorted, consider binary search or two pointers. If it shows connections, think graph traversal.\n\nBefore coding, write one sentence: \"This problem is about...\" That sentence usually points to the pattern.",
                'code_example' => null,
            ],
            [
                'module' => 'linear-patterns',
                'slug' => 'pps-hash-maps-and-counting',
                'title' => 'Hash Maps and Counting',
                'summary' => 'Count, group, and remember values you have already seen.',
                'video_url' => 'https://www.youtube.com/watch?v=vzdNOK2oB2E',
                'duration_minutes' => 30,
                'sort_order' => 1,
                'body_markdown' => "Hash maps are strongest when repeated searching would be too slow.\n\nUse them for frequency counts, duplicates, first-seen positions, matching pairs, and grouping. A map turns \"look through everything again\" into \"check what I already know\".\n\nWhen solving, decide what the key means and what value you need to store.",
                'code_example' => "def count_occurrences(numbers, target):\n    counts = {}\n    for number in numbers:\n        counts[number] = counts.get(number, 0) + 1\n    return counts.get(target, 0)",
            ],
            [
                'module' => 'linear-patterns',
                'slug' => 'pps-two-pointers',
                'title' => 'Two Pointers',
                'summary' => 'Move two indexes through ordered data to compare, reverse, or filter.',
                'video_url' => 'https://www.youtube.com/watch?v=cQ1Oz4ckceM',
                'duration_minutes' => 30,
                'sort_order' => 2,
                'body_markdown' => "Two pointers help when one pointer is not enough information.\n\nCommon moves include left and right moving inward, fast and slow moving at different speeds, or write and read indexes for in-place filtering.\n\nAsk what each pointer represents. If you can name the job of each pointer, the loop becomes much easier.",
                'code_example' => "def is_palindrome(text):\n    left, right = 0, len(text) - 1\n    while left < right:\n        if text[left] != text[right]:\n            return False\n        left += 1\n        right -= 1\n    return True",
            ],
            [
                'module' => 'linear-patterns',
                'slug' => 'pps-sliding-window-and-scans',
                'title' => 'Sliding Window and Scans',
                'summary' => 'Track a moving section of an array or string without starting over.',
                'video_url' => 'https://www.youtube.com/watch?v=GhRMbNKznBw',
                'duration_minutes' => 30,
                'sort_order' => 3,
                'body_markdown' => "Sliding window problems are usually about contiguous sections.\n\nThe window grows when you include a new item and shrinks when it breaks a rule. Simple scans are the smaller version: walk once, keep the best answer so far, and update only the state you need.\n\nLook for words like contiguous, subarray, substring, longest, shortest, or at most.",
                'code_example' => "def longest_positive_run(numbers):\n    best = 0\n    current = 0\n    for number in numbers:\n        current = current + 1 if number > 0 else 0\n        best = max(best, current)\n    return best",
            ],
            [
                'module' => 'search-and-branching',
                'slug' => 'pps-binary-search-pattern',
                'title' => 'Binary Search Pattern',
                'summary' => 'Use sorted order or yes/no conditions to remove half the search space.',
                'video_url' => 'https://www.youtube.com/watch?v=s4DPM8ct1pI',
                'duration_minutes' => 25,
                'sort_order' => 1,
                'body_markdown' => "Binary search is not only for finding a number in a sorted list.\n\nIt works whenever a decision splits the answer space into possible and impossible halves. That is why it can appear in scheduling, capacity, minimum valid answer, and sorted array problems.\n\nThe key question: after checking the middle, which side can I safely discard?",
                'code_example' => "def lower_bound(numbers, target):\n    left, right = 0, len(numbers)\n    while left < right:\n        middle = (left + right) // 2\n        if numbers[middle] < target:\n            left = middle + 1\n        else:\n            right = middle\n    return left",
            ],
            [
                'module' => 'search-and-branching',
                'slug' => 'pps-stacks-and-validation',
                'title' => 'Stacks and Validation',
                'summary' => 'Use last-in first-out memory for matching and nested structure problems.',
                'video_url' => 'https://www.youtube.com/watch?v=WTzjTskDFMg',
                'duration_minutes' => 25,
                'sort_order' => 2,
                'body_markdown' => "A stack remembers the most recent unresolved item.\n\nThis is perfect for parentheses, undo history, nested structures, monotonic stacks, and parsing. Push when you open a thing. Pop when you close or resolve it.\n\nIf the newest item matters first, reach for a stack.",
                'code_example' => "def is_balanced(text):\n    stack = []\n    pairs = {')': '(', ']': '[', '}': '{'}\n    for char in text:\n        if char in '([{':\n            stack.append(char)\n        elif char in pairs and (not stack or stack.pop() != pairs[char]):\n            return False\n    return not stack",
            ],
            [
                'module' => 'search-and-branching',
                'slug' => 'pps-graphs-backtracking-and-heaps',
                'title' => 'Graphs, Backtracking, and Heaps',
                'summary' => 'Recognize when a problem needs traversal, choices, or priority.',
                'video_url' => 'https://www.youtube.com/watch?v=pfiQ_PS1g8E',
                'duration_minutes' => 25,
                'sort_order' => 3,
                'body_markdown' => "Some problems are not simple lines.\n\nGraphs need traversal like BFS or DFS. Backtracking explores choices and undoes them when a path fails. Heaps keep the next best item ready without sorting everything every time.\n\nThese patterns are different, but they share one habit: keep only the state you need for the next decision.",
                'code_example' => null,
            ],
        ]);

        $patternsLessonsBySlug = $patternsLessons->mapWithKeys(function (array $lessonData) use ($patternsCourse, $patternsModules) {
            $lesson = Lesson::updateOrCreate(
                ['slug' => $lessonData['slug']],
                [
                    'course_id' => $patternsCourse->id,
                    'module_id' => $patternsModules[$lessonData['module']]->id,
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

        $algorithmCourse = Course::where('slug', 'algorithms-in-motion')->firstOrFail();
        $algorithmModules = collect([
            [
                'slug' => 'visual-foundations',
                'title' => 'Visual Foundations',
                'summary' => 'Build mental pictures for how data structures and algorithms move.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'core-structures',
                'title' => 'Core Structures',
                'summary' => 'Reason about arrays, strings, hash maps, recursion, and search.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'patterns-and-traversal',
                'title' => 'Patterns and Traversal',
                'summary' => 'Choose between sorting, graph traversal, and common problem-solving patterns.',
                'sort_order' => 3,
            ],
        ])->mapWithKeys(fn (array $module) => [
            $module['slug'] => Module::updateOrCreate(
                ['course_id' => $algorithmCourse->id, 'slug' => $module['slug']],
                [
                    'title' => $module['title'],
                    'summary' => $module['summary'],
                    'sort_order' => $module['sort_order'],
                ],
            ),
        ]);

        $algorithmLessons = collect([
            [
                'module' => 'visual-foundations',
                'slug' => 'aim-visual-dsa-crash-course',
                'title' => 'Learn Data Structures and Algorithms Visually',
                'summary' => 'Watch a visual crash course that connects arrays, hash maps, trees, graphs, searching, sorting, and recursion.',
                'video_url' => 'https://www.youtube.com/watch?v=RpLnQnurpLY',
                'duration_minutes' => 70,
                'sort_order' => 1,
                'body_markdown' => "Use this lesson as the visual map for the whole course.\n\nFocus on the shape of each idea: arrays store ordered values, hash maps connect keys to values, trees branch, graphs connect many paths, and algorithms are the rules for moving through those structures.\n\nAfter watching, try to explain each structure without code first. If you can describe the movement, the implementation becomes much easier.",
                'code_example' => null,
            ],
            [
                'module' => 'visual-foundations',
                'slug' => 'aim-big-o-and-trade-offs',
                'title' => 'Big O and Trade-Offs',
                'summary' => 'Compare solutions by how time and memory grow as input gets larger.',
                'video_url' => 'https://www.youtube.com/watch?v=Mo4vesaut8g',
                'duration_minutes' => 30,
                'sort_order' => 2,
                'body_markdown' => "Big O describes growth, not exact speed.\n\nA loop over every item is usually O(n). A nested loop over pairs is often O(n^2). Binary search is O(log n) because it cuts the remaining work in half each step.\n\nThe goal is not to memorize labels. The goal is to ask: what work repeats, how many times can it repeat, and what extra memory do I need?",
                'code_example' => "def count_pairs(numbers):\n    pairs = 0\n    for left in numbers:\n        for right in numbers:\n            pairs += 1\n    return pairs",
            ],
            [
                'module' => 'core-structures',
                'slug' => 'aim-arrays-strings-and-hash-maps',
                'title' => 'Arrays, Strings, and Hash Maps',
                'summary' => 'Use index order and fast lookups to simplify common problems.',
                'video_url' => 'https://www.youtube.com/watch?v=RBSGKlAvoiM',
                'duration_minutes' => 35,
                'sort_order' => 1,
                'body_markdown' => "Arrays and strings are ordered. That makes them good for scanning, comparing neighbors, slicing, and using two pointers.\n\nHash maps are lookup tables. They are useful when the question asks about counts, matching pairs, duplicates, or remembering something you already saw.\n\nWhen a problem says \"find\", \"count\", or \"seen before\", consider whether a hash map can turn repeated searching into one pass.",
                'code_example' => "def frequency(values):\n    counts = {}\n    for value in values:\n        counts[value] = counts.get(value, 0) + 1\n    return counts",
            ],
            [
                'module' => 'core-structures',
                'slug' => 'aim-recursion-and-call-stacks',
                'title' => 'Recursion and Call Stacks',
                'summary' => 'Break a problem into smaller versions of itself with a clear stopping point.',
                'video_url' => 'https://www.youtube.com/watch?v=IJDJ0kBx2LM',
                'duration_minutes' => 35,
                'sort_order' => 2,
                'body_markdown' => "Recursion works when a problem can be reduced into a smaller copy of the same problem.\n\nEvery recursive solution needs a base case. The base case stops the calls. Without it, the function keeps calling itself until the stack runs out.\n\nUse recursion when the structure branches naturally, like trees, graphs, backtracking, and divide-and-conquer sorting.",
                'code_example' => "def factorial(n):\n    if n <= 1:\n        return 1\n    return n * factorial(n - 1)",
            ],
            [
                'module' => 'core-structures',
                'slug' => 'aim-linear-and-binary-search',
                'title' => 'Linear and Binary Search',
                'summary' => 'Choose between checking every item and cutting the search space in half.',
                'video_url' => 'https://www.youtube.com/watch?v=j5uXyPJ0Pew',
                'duration_minutes' => 35,
                'sort_order' => 3,
                'body_markdown' => "Linear search works on any list because it checks items one by one.\n\nBinary search needs sorted data or a monotonic condition. It is powerful because each guess removes half of the remaining possibilities.\n\nBefore using binary search, ask: can I safely decide which half cannot contain the answer?",
                'code_example' => "def binary_search(numbers, target):\n    left, right = 0, len(numbers) - 1\n    while left <= right:\n        middle = (left + right) // 2\n        if numbers[middle] == target:\n            return middle\n        if numbers[middle] < target:\n            left = middle + 1\n        else:\n            right = middle - 1\n    return -1",
            ],
            [
                'module' => 'patterns-and-traversal',
                'slug' => 'aim-sorting-and-divide-and-conquer',
                'title' => 'Sorting and Divide and Conquer',
                'summary' => 'Use ordering to make comparison, grouping, and searching easier.',
                'video_url' => 'https://www.youtube.com/watch?v=RfXt_qHDEPw',
                'duration_minutes' => 40,
                'sort_order' => 1,
                'body_markdown' => "Sorting changes the shape of a problem.\n\nOnce values are ordered, duplicates sit beside each other, two-pointer checks become easier, and binary search becomes possible.\n\nDivide and conquer splits work into smaller pieces, solves them, then combines the answers. Merge sort is the classic example.",
                'code_example' => "def has_duplicate(numbers):\n    numbers = sorted(numbers)\n    for index in range(1, len(numbers)):\n        if numbers[index] == numbers[index - 1]:\n            return True\n    return False",
            ],
            [
                'module' => 'patterns-and-traversal',
                'slug' => 'aim-dfs-bfs-and-graphs',
                'title' => 'DFS, BFS, and Graph Thinking',
                'summary' => 'Traverse connected data by going deep first or level by level.',
                'video_url' => 'https://www.youtube.com/watch?v=tWVWeAqZ0WU',
                'duration_minutes' => 45,
                'sort_order' => 2,
                'body_markdown' => "Graphs model connections: people, pages, roads, dependencies, states, and many other systems.\n\nDFS explores one path deeply before backing up. BFS explores layer by layer and is often useful for shortest path in an unweighted graph.\n\nAlways track visited nodes so cycles do not trap your traversal.",
                'code_example' => "def dfs(graph, start):\n    visited = set()\n    stack = [start]\n    while stack:\n        node = stack.pop()\n        if node in visited:\n            continue\n        visited.add(node)\n        stack.extend(graph.get(node, []))\n    return visited",
            ],
            [
                'module' => 'patterns-and-traversal',
                'slug' => 'aim-choosing-the-right-pattern',
                'title' => 'Choosing the Right Pattern',
                'summary' => 'Practice recognizing which tool fits the shape of a problem.',
                'video_url' => 'https://www.youtube.com/watch?v=8hly31xKli0',
                'duration_minutes' => 10,
                'sort_order' => 3,
                'body_markdown' => "A strong problem solver matches clues to tools.\n\nOrdered data may suggest binary search or two pointers. Repeated lookup may suggest a hash map. Connected data may suggest DFS or BFS. Choices over a decision tree may suggest backtracking.\n\nWhen you get stuck, write down the input shape, the operation you repeat, and the information you need to remember.",
                'code_example' => null,
            ],
        ]);

        $algorithmLessonsBySlug = $algorithmLessons->mapWithKeys(function (array $lessonData) use ($algorithmCourse, $algorithmModules) {
            $lesson = Lesson::updateOrCreate(
                ['slug' => $lessonData['slug']],
                [
                    'course_id' => $algorithmCourse->id,
                    'module_id' => $algorithmModules[$lessonData['module']]->id,
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
                'course_id' => null,
                'lesson_id' => null,
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

        Exercise::where('slug', 'multiply-two-numbers')
            ->where('title', 'Multiply Two Numbers')
            ->update(['slug' => 'product-two-numbers', 'title' => 'Product of Two Numbers']);

        foreach ($this->practiceExerciseDefinitions() as $definition) {
            $this->seedPracticeExercise($definition, $admin->id);
        }

        $this->attachCourseExercises($algorithmLessonsBySlug, [
            'aim-arrays-strings-and-hash-maps' => [
                'count-items',
                'first-array-item',
                'sum-array',
                'contains-duplicate',
                'majority-element',
            ],
            'aim-recursion-and-call-stacks' => [
                'factorial-number',
                'fibonacci-number',
            ],
            'aim-linear-and-binary-search' => [
                'array-includes-number',
                'find-index',
                'binary-search',
            ],
            'aim-sorting-and-divide-and-conquer' => [
                'unique-sorted-numbers',
                'intersection-sorted',
                'valid-anagram',
            ],
            'aim-choosing-the-right-pattern' => [
                'two-sum-indices',
                'max-stock-profit',
                'move-zeroes-end',
                'longest-consecutive',
            ],
        ]);

        $this->attachCourseExercises($patternsLessonsBySlug, [
            'pps-choosing-a-pattern' => [
                'maximum-of-two',
                'minimum-of-two',
                'is-even-number',
                'absolute-value',
                'square-number',
            ],
            'pps-hash-maps-and-counting' => [
                'count-occurrences',
                'run-length-encode',
            ],
            'pps-two-pointers' => [
                'reverse-string',
                'palindrome-string',
            ],
            'pps-sliding-window-and-scans' => [
                'string-length',
                'first-character',
                'last-character',
                'uppercase-text',
                'repeat-word',
                'count-vowels',
                'count-even-numbers',
                'largest-in-array',
                'smallest-in-array',
                'average-array',
                'remove-spaces',
                'merge-strings',
                'positive-numbers',
                'double-values',
                'capitalize-words',
            ],
            'pps-binary-search-pattern' => [
                'missing-number',
                'rotate-array-right',
            ],
            'pps-stacks-and-validation' => [
                'balanced-parentheses',
            ],
            'pps-graphs-backtracking-and-heaps' => [
                'longest-word',
                'flatten-once',
                'chunk-array',
            ],
        ]);

        Achievement::firstOrCreate(
            ['slug' => 'first-accepted-answer'],
            ['name' => 'First Accepted Answer', 'description' => 'Earned after your first accepted coding submission.', 'points' => 5],
        );
    }

    /**
     * @param  array<string, array<int, string>>  $lessonExercises
     */
    private function attachCourseExercises($lessonsBySlug, array $lessonExercises): void
    {
        foreach ($lessonExercises as $lessonSlug => $exerciseSlugs) {
            $lesson = $lessonsBySlug->get($lessonSlug);

            if (! $lesson) {
                continue;
            }

            Exercise::whereIn('slug', $exerciseSlugs)->update([
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function practiceExerciseDefinitions(): array
    {
        return [
            $this->exerciseDefinition('subtract-two-numbers', 'Subtract Two Numbers', 'Return the difference between two integers.', 'EASY', 'subtract', [['a', 'number'], ['b', 'number']], 'number', ['functions', 'arithmetic'], 'Write a function named `subtract` that returns `a - b`.', ['python' => 'return a - b', 'javascript' => 'return a - b;', 'typescript' => 'return a - b;'], [[['a' => 7, 'b' => 2], 5], [['a' => -3, 'b' => 4], -7]]),
            $this->exerciseDefinition('product-two-numbers', 'Product of Two Numbers', 'Return the product of two integers.', 'EASY', 'multiply', [['a', 'number'], ['b', 'number']], 'number', ['functions', 'arithmetic'], 'Write a function named `multiply` that returns the product of two numbers.', ['python' => 'return a * b', 'javascript' => 'return a * b;', 'typescript' => 'return a * b;'], [[['a' => 6, 'b' => 7], 42], [['a' => -3, 'b' => 5], -15]]),
            $this->exerciseDefinition('maximum-of-two', 'Maximum of Two', 'Return the larger of two numbers.', 'EASY', 'maximum', [['a', 'number'], ['b', 'number']], 'number', ['conditionals', 'arithmetic'], 'Write a function named `maximum` that returns the larger number.', ['python' => 'return max(a, b)', 'javascript' => 'return Math.max(a, b);', 'typescript' => 'return Math.max(a, b);'], [[['a' => 8, 'b' => 3], 8], [['a' => -2, 'b' => 4], 4]]),
            $this->exerciseDefinition('minimum-of-two', 'Minimum of Two', 'Return the smaller of two numbers.', 'EASY', 'minimum', [['a', 'number'], ['b', 'number']], 'number', ['conditionals', 'arithmetic'], 'Write a function named `minimum` that returns the smaller number.', ['python' => 'return min(a, b)', 'javascript' => 'return Math.min(a, b);', 'typescript' => 'return Math.min(a, b);'], [[['a' => 8, 'b' => 3], 3], [['a' => -2, 'b' => 4], -2]]),
            $this->exerciseDefinition('is-even-number', 'Is Even Number', 'Check whether a number is even.', 'EASY', 'is_even', [['number', 'number']], 'boolean', ['conditionals', 'arithmetic'], 'Write a function named `is_even` that returns true when the number is even.', ['python' => 'return number % 2 == 0', 'javascript' => 'return number % 2 === 0;', 'typescript' => 'return number % 2 === 0;'], [[['number' => 10], true], [['number' => 7], false]]),
            $this->exerciseDefinition('absolute-value', 'Absolute Value', 'Return the positive distance from zero.', 'EASY', 'absolute_value', [['number', 'number']], 'number', ['math', 'conditionals'], 'Write a function named `absolute_value` that returns the absolute value of the number.', ['python' => 'return abs(number)', 'javascript' => 'return Math.abs(number);', 'typescript' => 'return Math.abs(number);'], [[['number' => -9], 9], [['number' => 12], 12]]),
            $this->exerciseDefinition('square-number', 'Square Number', 'Return a number multiplied by itself.', 'EASY', 'square', [['number', 'number']], 'number', ['functions', 'arithmetic'], 'Write a function named `square` that returns `number * number`.', ['python' => 'return number * number', 'javascript' => 'return number * number;', 'typescript' => 'return number * number;'], [[['number' => 5], 25], [['number' => -4], 16]]),
            $this->exerciseDefinition('string-length', 'String Length', 'Return the number of characters in a string.', 'EASY', 'string_length', [['text', 'string']], 'number', ['strings'], 'Write a function named `string_length` that returns how many characters are in `text`.', ['python' => 'return len(text)', 'javascript' => 'return text.length;', 'typescript' => 'return text.length;'], [[['text' => 'coddy'], 5], [['text' => ''], 0]]),
            $this->exerciseDefinition('first-character', 'First Character', 'Return the first character of a string.', 'EASY', 'first_character', [['text', 'string']], 'string', ['strings'], 'Write a function named `first_character` that returns the first character, or an empty string for empty input.', ['python' => 'return text[0] if text else ""', 'javascript' => 'return text[0] || "";', 'typescript' => 'return text[0] || "";'], [[['text' => 'array'], 'a'], [['text' => ''], '']]),
            $this->exerciseDefinition('last-character', 'Last Character', 'Return the last character of a string.', 'EASY', 'last_character', [['text', 'string']], 'string', ['strings'], 'Write a function named `last_character` that returns the final character, or an empty string for empty input.', ['python' => 'return text[-1] if text else ""', 'javascript' => 'return text.length ? text[text.length - 1] : "";', 'typescript' => 'return text.length ? text[text.length - 1] : "";'], [[['text' => 'array'], 'y'], [['text' => ''], '']]),
            $this->exerciseDefinition('uppercase-text', 'Uppercase Text', 'Convert text to uppercase.', 'EASY', 'uppercase_text', [['text', 'string']], 'string', ['strings'], 'Write a function named `uppercase_text` that returns the uppercase version of `text`.', ['python' => 'return text.upper()', 'javascript' => 'return text.toUpperCase();', 'typescript' => 'return text.toUpperCase();'], [[['text' => 'Coddy'], 'CODDY'], [['text' => 'abc123'], 'ABC123']]),
            $this->exerciseDefinition('repeat-word', 'Repeat Word', 'Repeat a string a given number of times.', 'EASY', 'repeat_word', [['word', 'string'], ['times', 'number']], 'string', ['strings', 'loops'], 'Write a function named `repeat_word` that repeats `word` exactly `times` times.', ['python' => 'return word * times', 'javascript' => 'return word.repeat(times);', 'typescript' => 'return word.repeat(times);'], [[['word' => 'ha', 'times' => 3], 'hahaha'], [['word' => 'x', 'times' => 0], '']]),
            $this->exerciseDefinition('count-items', 'Count Items', 'Return the number of items in an array.', 'EASY', 'count_items', [['items', 'number[]']], 'number', ['arrays'], 'Write a function named `count_items` that returns the array length.', ['python' => 'return len(items)', 'javascript' => 'return items.length;', 'typescript' => 'return items.length;'], [[['items' => [1, 2, 3]], 3], [['items' => []], 0]]),
            $this->exerciseDefinition('first-array-item', 'First Array Item', 'Return the first item in an array.', 'EASY', 'first_item', [['numbers', 'number[]']], 'number', ['arrays'], 'Write a function named `first_item` that returns the first number, or 0 if the array is empty.', ['python' => 'return numbers[0] if numbers else 0', 'javascript' => 'return numbers[0] ?? 0;', 'typescript' => 'return numbers[0] ?? 0;'], [[['numbers' => [9, 2, 1]], 9], [['numbers' => []], 0]]),
            $this->exerciseDefinition('sum-array', 'Sum Array', 'Return the sum of all numbers in an array.', 'EASY', 'sum_array', [['numbers', 'number[]']], 'number', ['arrays', 'loops'], 'Write a function named `sum_array` that returns the total of all numbers.', ['python' => 'return sum(numbers)', 'javascript' => 'return numbers.reduce((total, number) => total + number, 0);', 'typescript' => 'return numbers.reduce((total, number) => total + number, 0);'], [[['numbers' => [1, 2, 3, 4]], 10], [['numbers' => [-2, 5, 7]], 10]]),
            $this->exerciseDefinition('array-includes-number', 'Array Includes Number', 'Check whether an array contains a target.', 'EASY', 'includes_number', [['numbers', 'number[]'], ['target', 'number']], 'boolean', ['arrays', 'searching'], 'Write a function named `includes_number` that returns true if `target` is in `numbers`.', ['python' => 'return target in numbers', 'javascript' => 'return numbers.includes(target);', 'typescript' => 'return numbers.includes(target);'], [[['numbers' => [1, 4, 8], 'target' => 4], true], [['numbers' => [1, 4, 8], 'target' => 2], false]]),

            $this->exerciseDefinition('reverse-string', 'Reverse String', 'Return a string in reverse order.', 'MEDIUM', 'reverse_string', [['text', 'string']], 'string', ['strings'], 'Write a function named `reverse_string` that returns the reversed text.', ['python' => 'return text[::-1]', 'javascript' => 'return text.split("").reverse().join("");', 'typescript' => 'return text.split("").reverse().join("");'], [[['text' => 'hello'], 'olleh'], [['text' => 'coddy'], 'yddoc']]),
            $this->exerciseDefinition('count-vowels', 'Count Vowels', 'Count the vowels in a string.', 'MEDIUM', 'count_vowels', [['text', 'string']], 'number', ['strings', 'loops'], 'Write a function named `count_vowels` that counts a, e, i, o, and u.', ['python' => 'return sum(1 for char in text.lower() if char in "aeiou")', 'javascript' => 'return [...text.toLowerCase()].filter((char) => "aeiou".includes(char)).length;', 'typescript' => 'return [...text.toLowerCase()].filter((char) => "aeiou".includes(char)).length;'], [[['text' => 'education'], 5], [['text' => 'rhythm'], 0]]),
            $this->exerciseDefinition('palindrome-string', 'Palindrome String', 'Check whether a string reads the same backward.', 'MEDIUM', 'is_palindrome', [['text', 'string']], 'boolean', ['strings', 'two pointers'], 'Write a function named `is_palindrome` that returns true if the text is the same reversed.', ['python' => 'return text == text[::-1]', 'javascript' => 'return text === text.split("").reverse().join("");', 'typescript' => 'return text === text.split("").reverse().join("");'], [[['text' => 'level'], true], [['text' => 'coddy'], false]]),
            $this->exerciseDefinition('factorial-number', 'Factorial Number', 'Return n factorial.', 'MEDIUM', 'factorial', [['n', 'number']], 'number', ['loops', 'math'], 'Write a function named `factorial` that returns the product from 1 to n.', ['python' => "total = 1\n    for value in range(2, n + 1):\n        total *= value\n    return total", 'javascript' => "let total = 1;\n  for (let value = 2; value <= n; value++) total *= value;\n  return total;", 'typescript' => "let total = 1;\n  for (let value = 2; value <= n; value++) total *= value;\n  return total;"], [[['n' => 5], 120], [['n' => 0], 1]]),
            $this->exerciseDefinition('fibonacci-number', 'Fibonacci Number', 'Return the nth Fibonacci number.', 'MEDIUM', 'fibonacci', [['n', 'number']], 'number', ['loops', 'dynamic programming'], 'Write a function named `fibonacci` where fibonacci(0) is 0 and fibonacci(1) is 1.', ['python' => "a, b = 0, 1\n    for _ in range(n):\n        a, b = b, a + b\n    return a", 'javascript' => "let a = 0;\n  let b = 1;\n  for (let index = 0; index < n; index++) {\n    [a, b] = [b, a + b];\n  }\n  return a;", 'typescript' => "let a = 0;\n  let b = 1;\n  for (let index = 0; index < n; index++) {\n    [a, b] = [b, a + b];\n  }\n  return a;"], [[['n' => 7], 13], [['n' => 1], 1]]),
            $this->exerciseDefinition('count-even-numbers', 'Count Even Numbers', 'Count even values in an array.', 'MEDIUM', 'count_even_numbers', [['numbers', 'number[]']], 'number', ['arrays', 'loops'], 'Write a function named `count_even_numbers` that returns how many numbers are even.', ['python' => 'return sum(1 for number in numbers if number % 2 == 0)', 'javascript' => 'return numbers.filter((number) => number % 2 === 0).length;', 'typescript' => 'return numbers.filter((number) => number % 2 === 0).length;'], [[['numbers' => [1, 2, 3, 4, 6]], 3], [['numbers' => [1, 3, 5]], 0]]),
            $this->exerciseDefinition('largest-in-array', 'Largest in Array', 'Return the largest number in an array.', 'MEDIUM', 'largest_number', [['numbers', 'number[]']], 'number', ['arrays'], 'Write a function named `largest_number` that returns the largest value.', ['python' => 'return max(numbers)', 'javascript' => 'return Math.max(...numbers);', 'typescript' => 'return Math.max(...numbers);'], [[['numbers' => [3, 8, 2, 7]], 8], [['numbers' => [-5, -2, -9]], -2]]),
            $this->exerciseDefinition('smallest-in-array', 'Smallest in Array', 'Return the smallest number in an array.', 'MEDIUM', 'smallest_number', [['numbers', 'number[]']], 'number', ['arrays'], 'Write a function named `smallest_number` that returns the smallest value.', ['python' => 'return min(numbers)', 'javascript' => 'return Math.min(...numbers);', 'typescript' => 'return Math.min(...numbers);'], [[['numbers' => [3, 8, 2, 7]], 2], [['numbers' => [-5, -2, -9]], -9]]),
            $this->exerciseDefinition('average-array', 'Average Array', 'Return the average of an array.', 'MEDIUM', 'average_array', [['numbers', 'number[]']], 'number', ['arrays', 'math'], 'Write a function named `average_array` that returns the average value.', ['python' => 'return sum(numbers) / len(numbers)', 'javascript' => 'return numbers.reduce((total, number) => total + number, 0) / numbers.length;', 'typescript' => 'return numbers.reduce((total, number) => total + number, 0) / numbers.length;'], [[['numbers' => [2, 4, 6]], 4], [['numbers' => [10, 20]], 15]]),
            $this->exerciseDefinition('remove-spaces', 'Remove Spaces', 'Remove all spaces from text.', 'MEDIUM', 'remove_spaces', [['text', 'string']], 'string', ['strings'], 'Write a function named `remove_spaces` that removes space characters.', ['python' => 'return text.replace(" ", "")', 'javascript' => 'return text.replaceAll(" ", "");', 'typescript' => 'return text.replaceAll(" ", "");'], [[['text' => 'hello world'], 'helloworld'], [['text' => 'a b c'], 'abc']]),
            $this->exerciseDefinition('find-index', 'Find Index', 'Return the first index of a target number.', 'MEDIUM', 'find_index', [['numbers', 'number[]'], ['target', 'number']], 'number', ['arrays', 'searching'], 'Write a function named `find_index` that returns the target index, or -1.', ['python' => 'return numbers.index(target) if target in numbers else -1', 'javascript' => 'return numbers.indexOf(target);', 'typescript' => 'return numbers.indexOf(target);'], [[['numbers' => [4, 6, 8], 'target' => 6], 1], [['numbers' => [4, 6, 8], 'target' => 5], -1]]),
            $this->exerciseDefinition('contains-duplicate', 'Contains Duplicate', 'Check whether an array has repeated numbers.', 'MEDIUM', 'contains_duplicate', [['numbers', 'number[]']], 'boolean', ['arrays', 'sets'], 'Write a function named `contains_duplicate` that returns true if any value appears twice.', ['python' => 'return len(set(numbers)) != len(numbers)', 'javascript' => 'return new Set(numbers).size !== numbers.length;', 'typescript' => 'return new Set(numbers).size !== numbers.length;'], [[['numbers' => [1, 2, 3, 2]], true], [['numbers' => [1, 2, 3]], false]]),
            $this->exerciseDefinition('count-occurrences', 'Count Occurrences', 'Count how many times a target appears.', 'MEDIUM', 'count_occurrences', [['numbers', 'number[]'], ['target', 'number']], 'number', ['arrays', 'loops'], 'Write a function named `count_occurrences` that counts target matches.', ['python' => 'return numbers.count(target)', 'javascript' => 'return numbers.filter((number) => number === target).length;', 'typescript' => 'return numbers.filter((number) => number === target).length;'], [[['numbers' => [1, 2, 2, 3], 'target' => 2], 2], [['numbers' => [1, 2, 3], 'target' => 9], 0]]),
            $this->exerciseDefinition('merge-strings', 'Merge Strings', 'Join two strings together.', 'MEDIUM', 'merge_strings', [['left', 'string'], ['right', 'string']], 'string', ['strings'], 'Write a function named `merge_strings` that returns `left` followed by `right`.', ['python' => 'return left + right', 'javascript' => 'return left + right;', 'typescript' => 'return left + right;'], [[['left' => 'code', 'right' => 'dy'], 'codedy'], [['left' => '', 'right' => 'test'], 'test']]),
            $this->exerciseDefinition('positive-numbers', 'Positive Numbers', 'Return only positive numbers.', 'MEDIUM', 'positive_numbers', [['numbers', 'number[]']], 'number[]', ['arrays', 'filtering'], 'Write a function named `positive_numbers` that returns values greater than zero.', ['python' => 'return [number for number in numbers if number > 0]', 'javascript' => 'return numbers.filter((number) => number > 0);', 'typescript' => 'return numbers.filter((number) => number > 0);'], [[['numbers' => [-2, 0, 3, 5]], [3, 5]], [['numbers' => [-1, -5]], []]]),
            $this->exerciseDefinition('double-values', 'Double Values', 'Double every number in an array.', 'MEDIUM', 'double_values', [['numbers', 'number[]']], 'number[]', ['arrays', 'mapping'], 'Write a function named `double_values` that returns a new array with every value doubled.', ['python' => 'return [number * 2 for number in numbers]', 'javascript' => 'return numbers.map((number) => number * 2);', 'typescript' => 'return numbers.map((number) => number * 2);'], [[['numbers' => [1, 2, 3]], [2, 4, 6]], [['numbers' => [-1, 4]], [-2, 8]]]),
            $this->exerciseDefinition('capitalize-words', 'Capitalize Words', 'Capitalize the first letter of each word.', 'MEDIUM', 'capitalize_words', [['text', 'string']], 'string', ['strings'], 'Write a function named `capitalize_words` that capitalizes each space-separated word.', ['python' => 'return " ".join(word[:1].upper() + word[1:] for word in text.split(" "))', 'javascript' => 'return text.split(" ").map((word) => word.slice(0, 1).toUpperCase() + word.slice(1)).join(" ");', 'typescript' => 'return text.split(" ").map((word) => word.slice(0, 1).toUpperCase() + word.slice(1)).join(" ");'], [[['text' => 'hello coddy'], 'Hello Coddy'], [['text' => 'java script'], 'Java Script']]),

            $this->exerciseDefinition('two-sum-indices', 'Two Sum Indices', 'Return indices of two numbers that add to target.', 'HARD', 'two_sum', [['numbers', 'number[]'], ['target', 'number']], 'number[]', ['arrays', 'hash table'], 'Write a function named `two_sum` that returns the indices of the first valid pair.', ['python' => "seen = {}\n    for index, number in enumerate(numbers):\n        need = target - number\n        if need in seen:\n            return [seen[need], index]\n        seen[number] = index\n    return []", 'javascript' => "const seen = new Map();\n  for (let index = 0; index < numbers.length; index++) {\n    const need = target - numbers[index];\n    if (seen.has(need)) return [seen.get(need), index];\n    seen.set(numbers[index], index);\n  }\n  return [];", 'typescript' => "const seen = new Map<number, number>();\n  for (let index = 0; index < numbers.length; index++) {\n    const need = target - numbers[index];\n    if (seen.has(need)) return [seen.get(need)!, index];\n    seen.set(numbers[index], index);\n  }\n  return [];"], [[['numbers' => [2, 7, 11, 15], 'target' => 9], [0, 1]], [['numbers' => [3, 2, 4], 'target' => 6], [1, 2]]]),
            $this->exerciseDefinition('longest-word', 'Longest Word', 'Return the longest word from an array.', 'HARD', 'longest_word', [['words', 'string[]']], 'string', ['arrays', 'strings'], 'Write a function named `longest_word` that returns the first longest word.', ['python' => 'return max(words, key=len) if words else ""', 'javascript' => 'return words.reduce((best, word) => word.length > best.length ? word : best, "");', 'typescript' => 'return words.reduce((best, word) => word.length > best.length ? word : best, "");'], [[['words' => ['tree', 'algorithm', 'code']], 'algorithm'], [['words' => []], '']]),
            $this->exerciseDefinition('unique-sorted-numbers', 'Unique Sorted Numbers', 'Return sorted unique numbers.', 'HARD', 'unique_sorted', [['numbers', 'number[]']], 'number[]', ['arrays', 'sets', 'sorting'], 'Write a function named `unique_sorted` that removes duplicates and sorts ascending.', ['python' => 'return sorted(set(numbers))', 'javascript' => 'return [...new Set(numbers)].sort((a, b) => a - b);', 'typescript' => 'return [...new Set(numbers)].sort((a, b) => a - b);'], [[['numbers' => [3, 1, 2, 3, 1]], [1, 2, 3]], [['numbers' => []], []]]),
            $this->exerciseDefinition('intersection-sorted', 'Intersection Sorted', 'Return sorted values found in both arrays.', 'HARD', 'intersection_sorted', [['left', 'number[]'], ['right', 'number[]']], 'number[]', ['arrays', 'sets'], 'Write a function named `intersection_sorted` that returns unique shared numbers sorted ascending.', ['python' => 'return sorted(set(left).intersection(right))', 'javascript' => 'return [...new Set(left.filter((number) => right.includes(number)))].sort((a, b) => a - b);', 'typescript' => 'return [...new Set(left.filter((number) => right.includes(number)))].sort((a, b) => a - b);'], [[['left' => [1, 2, 2, 3], 'right' => [2, 3, 4]], [2, 3]], [['left' => [5], 'right' => [1, 2]], []]]),
            $this->exerciseDefinition('missing-number', 'Missing Number', 'Find the missing value from 0 to n.', 'HARD', 'missing_number', [['numbers', 'number[]']], 'number', ['arrays', 'math'], 'Write a function named `missing_number` that receives n unique values from 0 to n with one missing.', ['python' => 'return len(numbers) * (len(numbers) + 1) // 2 - sum(numbers)', 'javascript' => 'return (numbers.length * (numbers.length + 1)) / 2 - numbers.reduce((total, number) => total + number, 0);', 'typescript' => 'return (numbers.length * (numbers.length + 1)) / 2 - numbers.reduce((total, number) => total + number, 0);'], [[['numbers' => [3, 0, 1]], 2], [['numbers' => [0, 1]], 2]]),
            $this->exerciseDefinition('balanced-parentheses', 'Balanced Parentheses', 'Validate matching parentheses.', 'HARD', 'is_balanced', [['text', 'string']], 'boolean', ['strings', 'stack'], 'Write a function named `is_balanced` that checks parentheses, brackets, and braces.', ['python' => "stack = []\n    pairs = {')': '(', ']': '[', '}': '{'}\n    for char in text:\n        if char in '([{':\n            stack.append(char)\n        elif char in pairs:\n            if not stack or stack.pop() != pairs[char]:\n                return False\n    return not stack", 'javascript' => "const stack = [];\n  const pairs = { ')': '(', ']': '[', '}': '{' };\n  for (const char of text) {\n    if ('([{'.includes(char)) stack.push(char);\n    if (pairs[char] && stack.pop() !== pairs[char]) return false;\n  }\n  return stack.length === 0;", 'typescript' => "const stack: string[] = [];\n  const pairs: Record<string, string> = { ')': '(', ']': '[', '}': '{' };\n  for (const char of text) {\n    if ('([{'.includes(char)) stack.push(char);\n    if (pairs[char] && stack.pop() !== pairs[char]) return false;\n  }\n  return stack.length === 0;"], [[['text' => '([]){}'], true], [['text' => '([)]'], false]]),
            $this->exerciseDefinition('valid-anagram', 'Valid Anagram', 'Check whether two strings use the same letters.', 'HARD', 'is_anagram', [['left', 'string'], ['right', 'string']], 'boolean', ['strings', 'sorting'], 'Write a function named `is_anagram` that returns true when both strings contain the same characters.', ['python' => 'return sorted(left) == sorted(right)', 'javascript' => 'return left.split("").sort().join("") === right.split("").sort().join("");', 'typescript' => 'return left.split("").sort().join("") === right.split("").sort().join("");'], [[['left' => 'listen', 'right' => 'silent'], true], [['left' => 'rat', 'right' => 'car'], false]]),
            $this->exerciseDefinition('max-stock-profit', 'Max Stock Profit', 'Return the best profit from one buy and one sell.', 'HARD', 'max_profit', [['prices', 'number[]']], 'number', ['arrays', 'greedy'], 'Write a function named `max_profit` that returns the maximum profit from one transaction.', ['python' => "best = 0\n    lowest = prices[0] if prices else 0\n    for price in prices:\n        lowest = min(lowest, price)\n        best = max(best, price - lowest)\n    return best", 'javascript' => "let best = 0;\n  let lowest = prices[0] ?? 0;\n  for (const price of prices) {\n    lowest = Math.min(lowest, price);\n    best = Math.max(best, price - lowest);\n  }\n  return best;", 'typescript' => "let best = 0;\n  let lowest = prices[0] ?? 0;\n  for (const price of prices) {\n    lowest = Math.min(lowest, price);\n    best = Math.max(best, price - lowest);\n  }\n  return best;"], [[['prices' => [7, 1, 5, 3, 6, 4]], 5], [['prices' => [7, 6, 4, 3, 1]], 0]]),
            $this->exerciseDefinition('move-zeroes-end', 'Move Zeroes End', 'Move all zeroes to the end while keeping order.', 'HARD', 'move_zeroes', [['numbers', 'number[]']], 'number[]', ['arrays', 'two pointers'], 'Write a function named `move_zeroes` that returns a reordered array with zeroes at the end.', ['python' => "non_zero = [number for number in numbers if number != 0]\n    return non_zero + [0] * (len(numbers) - len(non_zero))", 'javascript' => "const nonZero = numbers.filter((number) => number !== 0);\n  return [...nonZero, ...Array(numbers.length - nonZero.length).fill(0)];", 'typescript' => "const nonZero = numbers.filter((number) => number !== 0);\n  return [...nonZero, ...Array(numbers.length - nonZero.length).fill(0)];"], [[['numbers' => [0, 1, 0, 3, 12]], [1, 3, 12, 0, 0]], [['numbers' => [0, 0, 1]], [1, 0, 0]]]),
            $this->exerciseDefinition('rotate-array-right', 'Rotate Array Right', 'Rotate an array right by k steps.', 'HARD', 'rotate_right', [['numbers', 'number[]'], ['k', 'number']], 'number[]', ['arrays'], 'Write a function named `rotate_right` that returns the array rotated right by k positions.', ['python' => "if not numbers:\n        return []\n    k = k % len(numbers)\n    return numbers[-k:] + numbers[:-k] if k else numbers", 'javascript' => "if (!numbers.length) return [];\n  k = k % numbers.length;\n  return k ? [...numbers.slice(-k), ...numbers.slice(0, -k)] : numbers;", 'typescript' => "if (!numbers.length) return [];\n  k = k % numbers.length;\n  return k ? [...numbers.slice(-k), ...numbers.slice(0, -k)] : numbers;"], [[['numbers' => [1, 2, 3, 4, 5], 'k' => 2], [4, 5, 1, 2, 3]], [['numbers' => [1, 2], 'k' => 3], [2, 1]]]),
            $this->exerciseDefinition('flatten-once', 'Flatten Once', 'Flatten one level of nested arrays.', 'HARD', 'flatten_once', [['matrix', 'number[][]']], 'number[]', ['arrays'], 'Write a function named `flatten_once` that turns a list of number arrays into one array.', ['python' => 'return [value for row in matrix for value in row]', 'javascript' => 'return matrix.flat();', 'typescript' => 'return matrix.flat();'], [[['matrix' => [[1, 2], [3], [4, 5]]], [1, 2, 3, 4, 5]], [['matrix' => [[], [7]]], [7]]]),
            $this->exerciseDefinition('chunk-array', 'Chunk Array', 'Split an array into chunks of a given size.', 'HARD', 'chunk_array', [['numbers', 'number[]'], ['size', 'number']], 'number[][]', ['arrays'], 'Write a function named `chunk_array` that returns groups of length `size` except possibly the last group.', ['python' => 'return [numbers[index:index + size] for index in range(0, len(numbers), size)]', 'javascript' => "const chunks = [];\n  for (let index = 0; index < numbers.length; index += size) chunks.push(numbers.slice(index, index + size));\n  return chunks;", 'typescript' => "const chunks: number[][] = [];\n  for (let index = 0; index < numbers.length; index += size) chunks.push(numbers.slice(index, index + size));\n  return chunks;"], [[['numbers' => [1, 2, 3, 4, 5], 'size' => 2], [[1, 2], [3, 4], [5]]], [['numbers' => [1, 2, 3], 'size' => 3], [[1, 2, 3]]]]),
            $this->exerciseDefinition('run-length-encode', 'Run Length Encode', 'Compress repeated characters.', 'HARD', 'run_length_encode', [['text', 'string']], 'string', ['strings'], 'Write a function named `run_length_encode` that converts repeated characters into character-count pairs.', ['python' => "if not text:\n        return \"\"\n    result = []\n    count = 1\n    for index in range(1, len(text)):\n        if text[index] == text[index - 1]:\n            count += 1\n        else:\n            result.append(text[index - 1] + str(count))\n            count = 1\n    result.append(text[-1] + str(count))\n    return \"\".join(result)", 'javascript' => "if (!text) return \"\";\n  const result = [];\n  let count = 1;\n  for (let index = 1; index < text.length; index++) {\n    if (text[index] === text[index - 1]) count++;\n    else {\n      result.push(text[index - 1] + count);\n      count = 1;\n    }\n  }\n  result.push(text[text.length - 1] + count);\n  return result.join(\"\");", 'typescript' => "if (!text) return \"\";\n  const result: string[] = [];\n  let count = 1;\n  for (let index = 1; index < text.length; index++) {\n    if (text[index] === text[index - 1]) count++;\n    else {\n      result.push(text[index - 1] + count);\n      count = 1;\n    }\n  }\n  result.push(text[text.length - 1] + count);\n  return result.join(\"\");"], [[['text' => 'aaabbc'], 'a3b2c1'], [['text' => ''], '']]),
            $this->exerciseDefinition('binary-search', 'Binary Search', 'Find a target in a sorted array.', 'HARD', 'binary_search', [['numbers', 'number[]'], ['target', 'number']], 'number', ['arrays', 'searching'], 'Write a function named `binary_search` that returns the target index, or -1 if missing.', ['python' => "left, right = 0, len(numbers) - 1\n    while left <= right:\n        middle = (left + right) // 2\n        if numbers[middle] == target:\n            return middle\n        if numbers[middle] < target:\n            left = middle + 1\n        else:\n            right = middle - 1\n    return -1", 'javascript' => "let left = 0;\n  let right = numbers.length - 1;\n  while (left <= right) {\n    const middle = Math.floor((left + right) / 2);\n    if (numbers[middle] === target) return middle;\n    if (numbers[middle] < target) left = middle + 1;\n    else right = middle - 1;\n  }\n  return -1;", 'typescript' => "let left = 0;\n  let right = numbers.length - 1;\n  while (left <= right) {\n    const middle = Math.floor((left + right) / 2);\n    if (numbers[middle] === target) return middle;\n    if (numbers[middle] < target) left = middle + 1;\n    else right = middle - 1;\n  }\n  return -1;"], [[['numbers' => [1, 3, 5, 7, 9], 'target' => 7], 3], [['numbers' => [1, 3, 5], 'target' => 2], -1]]),
            $this->exerciseDefinition('majority-element', 'Majority Element', 'Find the value that appears most often.', 'HARD', 'majority_element', [['numbers', 'number[]']], 'number', ['arrays', 'hash table'], 'Write a function named `majority_element` that returns the number appearing more than half the time.', ['python' => "counts = {}\n    for number in numbers:\n        counts[number] = counts.get(number, 0) + 1\n        if counts[number] > len(numbers) // 2:\n            return number\n    return 0", 'javascript' => "const counts = new Map();\n  for (const number of numbers) {\n    counts.set(number, (counts.get(number) || 0) + 1);\n    if (counts.get(number) > Math.floor(numbers.length / 2)) return number;\n  }\n  return 0;", 'typescript' => "const counts = new Map<number, number>();\n  for (const number of numbers) {\n    counts.set(number, (counts.get(number) || 0) + 1);\n    if ((counts.get(number) || 0) > Math.floor(numbers.length / 2)) return number;\n  }\n  return 0;"], [[['numbers' => [3, 2, 3]], 3], [['numbers' => [2, 2, 1, 1, 1, 2, 2]], 2]]),
            $this->exerciseDefinition('longest-consecutive', 'Longest Consecutive', 'Find the longest consecutive number streak.', 'HARD', 'longest_consecutive', [['numbers', 'number[]']], 'number', ['arrays', 'sets'], 'Write a function named `longest_consecutive` that returns the length of the longest consecutive sequence.', ['python' => "values = set(numbers)\n    best = 0\n    for number in values:\n        if number - 1 not in values:\n            length = 1\n            while number + length in values:\n                length += 1\n            best = max(best, length)\n    return best", 'javascript' => "const values = new Set(numbers);\n  let best = 0;\n  for (const number of values) {\n    if (!values.has(number - 1)) {\n      let length = 1;\n      while (values.has(number + length)) length++;\n      best = Math.max(best, length);\n    }\n  }\n  return best;", 'typescript' => "const values = new Set(numbers);\n  let best = 0;\n  for (const number of values) {\n    if (!values.has(number - 1)) {\n      let length = 1;\n      while (values.has(number + length)) length++;\n      best = Math.max(best, length);\n    }\n  }\n  return best;"], [[['numbers' => [100, 4, 200, 1, 3, 2]], 4], [['numbers' => [0, 3, 7, 2, 5, 8, 4, 6, 1]], 9]]),
        ];
    }

    /**
     * @param  array<int, array{0:string,1:string}>  $arguments
     * @param  array<int, string>  $tags
     * @param  array<string, string>  $solutionBodies
     * @param  array<int, array{0:array<string, mixed>,1:mixed}>  $examples
     * @return array<string, mixed>
     */
    private function exerciseDefinition(string $slug, string $title, string $summary, string $difficulty, string $functionName, array $arguments, string $returnType, array $tags, string $description, array $solutionBodies, array $examples): array
    {
        return compact('slug', 'title', 'summary', 'difficulty', 'functionName', 'arguments', 'returnType', 'tags', 'description', 'solutionBodies', 'examples');
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedPracticeExercise(array $definition, int $adminId): void
    {
        $exercise = Exercise::updateOrCreate(
            ['slug' => $definition['slug']],
            [
                'course_id' => null,
                'lesson_id' => null,
                'created_by' => $adminId,
                'title' => $definition['title'],
                'summary' => $definition['summary'],
                'difficulty' => $definition['difficulty'],
                'publication_status' => 'PUBLISHED',
                'published_at' => now(),
                'archived_at' => null,
            ],
        );

        $conceptIds = collect($definition['tags'])
            ->map(fn (string $tag) => Concept::firstOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => Str::headline($tag), 'description' => 'Practice problems related to '.Str::headline($tag).'.'],
            )->id)
            ->all();

        $exercise->concepts()->sync($conceptIds);

        $version = $exercise->versions()->where('version_number', 1)->first()
            ?? $exercise->versions()->make(['version_number' => 1]);

        $languagePayload = $this->languagePayload($definition);
        $visibleExamples = collect($definition['examples'])
            ->take(1)
            ->map(fn (array $example) => ['input' => $example[0], 'output' => $example[1]])
            ->values()
            ->all();

        $version->forceFill([
            'created_by' => $adminId,
            'title' => $definition['title'],
            'summary' => $definition['summary'],
            'description_markdown' => $definition['description'],
            'difficulty' => $definition['difficulty'],
            'constraints' => ['Inputs are valid for the stated function.', 'Return the result instead of printing it.'],
            'visible_examples' => $visibleExamples,
            'supported_languages' => array_keys($languagePayload['starter']),
            'starter_code_by_language' => $languagePayload['starter'],
            'function_signature_by_language' => $languagePayload['signatures'],
            'hints' => [
                ['order' => 1, 'content_markdown' => 'Start by using the function parameters directly.'],
                ['order' => 2, 'content_markdown' => 'Compare your returned value with each sample output.'],
            ],
            'official_solutions_by_language' => $languagePayload['solutions'],
            'explanation_markdown' => 'The tests call your function with prepared inputs and compare the returned value with the expected output.',
            'time_limit_ms' => 1000,
            'memory_limit_mb' => 128,
            'points' => $definition['difficulty'] === 'EASY' ? 10 : ($definition['difficulty'] === 'MEDIUM' ? 20 : 30),
            'status' => 'PUBLISHED',
            'published_at' => now(),
            'archived_at' => null,
        ])->save();

        foreach ($languagePayload['starter'] as $language => $code) {
            $version->starterCodes()->updateOrCreate(['language' => $language], ['code' => $code]);
        }

        foreach ($version->hints as $hint) {
            $version->hintRecords()->updateOrCreate(
                ['hint_order' => $hint['order']],
                ['content_markdown' => $hint['content_markdown']],
            );
        }

        foreach ($languagePayload['solutions'] as $language => $code) {
            $version->officialSolutions()->updateOrCreate(
                ['language' => $language],
                ['code' => $code, 'explanation_markdown' => $version->explanation_markdown],
            );
        }

        $bundle = $version->testBundle()->updateOrCreate(
            ['version_label' => 'v1'],
            ['status' => 'PUBLISHED', 'checksum' => hash('sha256', json_encode($definition['examples']))],
        );

        $bundle->testCases()->delete();
        $bundle->testCases()->createMany(
            collect($definition['examples'])->map(fn (array $example, int $index) => [
                'visibility' => $index === 0 ? 'VISIBLE' : 'HIDDEN',
                'name' => ($index === 0 ? 'sample case' : 'hidden case').' '.($index + 1),
                'input' => json_encode($example[0], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'expected_output' => json_encode($example[1], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'sort_order' => $index + 1,
            ])->all(),
        );

        $exercise->update(['active_version_id' => $version->id]);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array{starter:array<string, string>, signatures:array<string, string>, solutions:array<string, string>}
     */
    private function languagePayload(array $definition): array
    {
        $argumentNames = collect($definition['arguments'])->pluck(0)->all();
        $pythonArguments = implode(', ', $argumentNames);
        $javascriptArguments = implode(', ', $argumentNames);
        $typescriptArguments = collect($definition['arguments'])
            ->map(fn (array $argument) => $argument[0].': '.$this->typescriptType($argument[1]))
            ->implode(', ');
        $phpArguments = collect($argumentNames)
            ->map(fn (string $argument) => '$'.$argument)
            ->implode(', ');
        $cppArguments = collect($definition['arguments'])
            ->map(fn (array $argument) => $this->cppSeederType($argument[1]).' '.$argument[0])
            ->implode(', ');
        $typescriptReturn = $this->typescriptType($definition['returnType']);
        $cppReturn = $this->cppSeederType($definition['returnType']);

        return [
            'starter' => [
                'python' => "def {$definition['functionName']}({$pythonArguments}):\n    # write your code here\n    return ".$this->pythonDefault($definition['returnType']),
                'javascript' => "function {$definition['functionName']}({$javascriptArguments}) {\n  // write your code here\n  return ".$this->javascriptDefault($definition['returnType']).";\n}",
                'typescript' => "function {$definition['functionName']}({$typescriptArguments}): {$typescriptReturn} {\n  // write your code here\n  return ".$this->javascriptDefault($definition['returnType']).";\n}",
                'php' => "function {$definition['functionName']}({$phpArguments}) {\n    // write your code here\n    return ".$this->phpDefault($definition['returnType']).";\n}",
                'cpp' => "{$cppReturn} {$definition['functionName']}({$cppArguments}) {\n    // write your code here\n    return ".$this->cppDefault($definition['returnType']).";\n}",
            ],
            'signatures' => [
                'python' => "def {$definition['functionName']}({$pythonArguments}):",
                'javascript' => "function {$definition['functionName']}({$javascriptArguments})",
                'typescript' => "function {$definition['functionName']}({$typescriptArguments}): {$typescriptReturn}",
                'php' => "function {$definition['functionName']}({$phpArguments})",
                'cpp' => "{$cppReturn} {$definition['functionName']}({$cppArguments})",
            ],
            'solutions' => [
                'python' => "def {$definition['functionName']}({$pythonArguments}):\n    ".$definition['solutionBodies']['python'],
                'javascript' => "function {$definition['functionName']}({$javascriptArguments}) {\n  ".$definition['solutionBodies']['javascript']."\n}",
                'typescript' => "function {$definition['functionName']}({$typescriptArguments}): {$typescriptReturn} {\n  ".$definition['solutionBodies']['typescript']."\n}",
            ],
        ];
    }

    private function typescriptType(string $type): string
    {
        return match ($type) {
            'number[]' => 'number[]',
            'number[][]' => 'number[][]',
            'string[]' => 'string[]',
            'boolean' => 'boolean',
            'string' => 'string',
            default => 'number',
        };
    }

    private function pythonDefault(string $type): string
    {
        return match ($type) {
            'boolean' => 'False',
            'string' => '""',
            'number[]', 'number[][]', 'string[]' => '[]',
            default => '0',
        };
    }

    private function javascriptDefault(string $type): string
    {
        return match ($type) {
            'boolean' => 'false',
            'string' => '""',
            'number[]', 'number[][]', 'string[]' => '[]',
            default => '0',
        };
    }

    private function phpDefault(string $type): string
    {
        return match ($type) {
            'boolean' => 'false',
            'string' => '""',
            'number[]', 'number[][]', 'string[]' => '[]',
            default => '0',
        };
    }

    private function cppSeederType(string $type): string
    {
        return match ($type) {
            'boolean' => 'bool',
            'string' => 'string',
            'number[]' => 'vector<int>',
            'number[][]' => 'vector<vector<int>>',
            'string[]' => 'vector<string>',
            default => 'int',
        };
    }

    private function cppDefault(string $type): string
    {
        return match ($type) {
            'boolean' => 'false',
            'string' => '""',
            'number[]' => 'vector<int>{}',
            'number[][]' => 'vector<vector<int>>{}',
            'string[]' => 'vector<string>{}',
            default => '0',
        };
    }
}
