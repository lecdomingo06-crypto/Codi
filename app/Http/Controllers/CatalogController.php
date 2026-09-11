<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\ActivityCalendarService;
use App\Services\CoursePlayerService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function courses(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $courses = Course::query()
            ->where('publication_status', 'PUBLISHED')
            ->withCount([
                'lessons as published_lessons_count' => fn ($query) => $query->where('publication_status', 'PUBLISHED'),
            ])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->orderBy('title')
            ->get();

        $completedByCourse = DB::table('lesson_progress')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->where('lesson_progress.user_id', request()->user()->id)
            ->where('lesson_progress.status', 'COMPLETED')
            ->where('lessons.publication_status', 'PUBLISHED')
            ->groupBy('lessons.course_id')
            ->pluck(DB::raw('count(*)'), 'lessons.course_id');

        $courses->each(function (Course $course) use ($completedByCourse): void {
            $course->completed_lessons_count = (int) ($completedByCourse[$course->id] ?? 0);
            $course->progress_percent = $course->published_lessons_count > 0
                ? (int) round(($course->completed_lessons_count / $course->published_lessons_count) * 100)
                : 0;
        });

        $categoryDescriptions = [
            'Data Structures & Algorithms' => 'Build the core problem-solving patterns used in technical interviews and real software.',
            'System Design' => 'Learn how to reason about reliable, scalable services from first principles.',
            'Python' => 'Practice expressive Python with short lessons and focused exercises.',
            'Web Development' => 'Create useful web experiences by understanding the browser, server, and everything between.',
            'Programming' => 'Develop the fundamentals that make every new language easier to learn.',
        ];

        $lessons = Lesson::query()
            ->where('publication_status', 'PUBLISHED')
            ->with('course')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($query) => $query->where('title', 'like', "%{$search}%"));
            }))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('courses.index', [
            'courses' => $courses,
            'categoryDescriptions' => $categoryDescriptions,
            'lessons' => $lessons,
            'search' => $search,
        ]);
    }

    public function index(Request $request, ActivityCalendarService $calendarService): View|JsonResponse
    {
        $difficulty = $request->query('difficulty');
        $sort = $request->query('sort', 'recent');
        $search = trim((string) $request->query('search', ''));
        $user = $request->user();
        $streak = $user->streak()->firstOrCreate(['user_id' => $user->id]);

        $courses = Course::query()
            ->where('publication_status', 'PUBLISHED')
            ->withCount('lessons', 'exercises')
            ->orderBy('title')
            ->get();

        $exerciseBase = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id');

        $exercises = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->with('concepts')
            ->withCount([
                'submissions as accepted_submissions_count' => fn ($query) => $query->where('verdict', 'ACCEPTED'),
                'submissions as total_submissions_count',
                'submissions as user_accepted_count' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where('verdict', 'ACCEPTED'),
                'submissions as user_attempts_count' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->when(in_array($difficulty, Exercise::DIFFICULTIES, true), fn ($query) => $query->where('difficulty', $difficulty))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('concepts', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->when($sort === 'difficulty', fn ($query) => $query->orderByRaw("CASE difficulty WHEN 'EASY' THEN 1 WHEN 'MEDIUM' THEN 2 WHEN 'HARD' THEN 3 ELSE 4 END"))
            ->when($sort !== 'difficulty', fn ($query) => $query->latest())
            ->get();

        $topicCounts = Concept::query()
            ->withCount(['exercises as published_exercises_count' => fn ($query) => $query
                ->where('publication_status', 'PUBLISHED')
                ->whereNotNull('active_version_id')])
            ->orderByDesc('published_exercises_count')
            ->orderBy('name')
            ->get()
            ->where('published_exercises_count', '>', 0)
            ->values();

        $difficultyCounts = (clone $exerciseBase)
            ->selectRaw('difficulty, count(*) as total')
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');

        $acceptedExerciseIds = $user->submissions()
            ->where('verdict', 'ACCEPTED')
            ->distinct()
            ->pluck('exercise_id');

        $solvedDifficultyCounts = Exercise::query()
            ->whereIn('id', $acceptedExerciseIds)
            ->selectRaw('difficulty, count(*) as total')
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');

        $totalProblems = (clone $exerciseBase)->count();
        $solvedCount = $acceptedExerciseIds->count();

        if ($request->wantsJson()) {
            return response()->json([
                'courses' => $courses,
                'exercises' => $exercises,
            ]);
        }

        return view('catalog.index', [
            'courses' => $courses,
            'exercises' => $exercises,
            'difficulty' => $difficulty,
            'sort' => $sort,
            'search' => $search,
            'topics' => $topicCounts,
            'difficultyCounts' => $difficultyCounts,
            'solvedDifficultyCounts' => $solvedDifficultyCounts,
            'totalProblems' => $totalProblems,
            'solvedCount' => $solvedCount,
            'calendar' => $calendarService->yearlyCalendar($user),
            'streak' => $streak,
            'monthCalendar' => $this->monthCalendar($request),
            'recentSubmissions' => $user->submissions()->with('exercise')->latest()->limit(5)->get(),
        ]);
    }

    public function show(Course $course, CoursePlayerService $playerService): View
    {
        abort_unless($course->publication_status === 'PUBLISHED', 404);

        return view('catalog.show', $playerService->build($course, request()->user()) + [
            'isAdmin' => request()->user()->isAdmin(),
            'isEnrolled' => request()->user()->enrollments()->where('course_id', $course->id)->exists(),
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->publication_status === 'PUBLISHED', 404);
        abort_unless($request->user()->isUser(), 403);

        Enrollment::firstOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            ['enrolled_at' => now()],
        );

        return back()->with('status', 'Enrollment saved.');
    }

    /**
     * @return array{label:string, prev_month:string, next_month:string, days:array<int, array{date:string, day:int, in_month:bool, is_today:bool, answer_count:int, accepted_answer_count:int}>}
     */
    private function monthCalendar(Request $request): array
    {
        $user = $request->user();
        $timezone = $user->timezone ?: config('app.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $requestedMonth = (string) $request->query('month', '');
        $monthStart = $today->startOfMonth();

        if (preg_match('/^\d{4}-\d{2}$/', $requestedMonth) === 1) {
            try {
                $parsedMonth = CarbonImmutable::createFromFormat('Y-m-d', "{$requestedMonth}-01", $timezone);

                if ($parsedMonth !== false && $parsedMonth->format('Y-m') === $requestedMonth) {
                    $monthStart = $parsedMonth->startOfMonth();
                }
            } catch (\Throwable) {
                $monthStart = $today->startOfMonth();
            }
        }

        $gridStart = $monthStart->startOfWeek(CarbonInterface::SUNDAY);
        $gridEnd = $monthStart->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);

        $activities = $user->dailyActivities()
            ->whereBetween('activity_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->keyBy(fn ($activity) => substr((string) $activity->getRawOriginal('activity_date'), 0, 10));

        $days = [];

        for ($cursor = $gridStart; $cursor->lessThanOrEqualTo($gridEnd); $cursor = $cursor->addDay()) {
            $key = $cursor->toDateString();
            $activity = $activities->get($key);

            $days[] = [
                'date' => $key,
                'day' => (int) $cursor->format('j'),
                'in_month' => $cursor->month === $monthStart->month && $cursor->year === $monthStart->year,
                'is_today' => $cursor->isSameDay($today),
                'answer_count' => (int) ($activity?->answer_count ?? 0),
                'accepted_answer_count' => (int) ($activity?->accepted_answer_count ?? 0),
            ];
        }

        return [
            'label' => $monthStart->format('F Y'),
            'prev_month' => $monthStart->subMonth()->format('Y-m'),
            'next_month' => $monthStart->addMonth()->format('Y-m'),
            'days' => $days,
        ];
    }
}
