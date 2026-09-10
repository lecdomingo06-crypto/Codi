<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function courses(): View
    {
        $courses = Course::query()
            ->where('publication_status', 'PUBLISHED')
            ->withCount([
                'lessons as published_lessons_count' => fn ($query) => $query->where('publication_status', 'PUBLISHED'),
            ])
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

        return view('courses.index', [
            'courses' => $courses,
            'categoryDescriptions' => $categoryDescriptions,
        ]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $difficulty = $request->query('difficulty');
        $sort = $request->query('sort', 'recent');

        $courses = Course::query()
            ->where('publication_status', 'PUBLISHED')
            ->withCount('lessons', 'exercises')
            ->orderBy('title')
            ->get();

        $exercises = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->with('concepts')
            ->when(in_array($difficulty, Exercise::DIFFICULTIES, true), fn ($query) => $query->where('difficulty', $difficulty))
            ->when($sort === 'difficulty', fn ($query) => $query->orderByRaw("CASE difficulty WHEN 'EASY' THEN 1 WHEN 'MEDIUM' THEN 2 WHEN 'HARD' THEN 3 ELSE 4 END"))
            ->when($sort !== 'difficulty', fn ($query) => $query->latest())
            ->get();

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
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->publication_status === 'PUBLISHED', 404);

        $course->load([
            'lessons' => fn ($query) => $query->where('publication_status', 'PUBLISHED'),
            'modules.lessons' => fn ($query) => $query->where('publication_status', 'PUBLISHED'),
            'exercises' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->with('concepts'),
        ]);

        $completedLessons = DB::table('lesson_progress')
            ->where('user_id', request()->user()->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->where('status', 'COMPLETED')
            ->count();

        $course->completed_lessons_count = $completedLessons;
        $course->published_lessons_count = $course->lessons->count();
        $course->progress_percent = $course->published_lessons_count > 0
            ? (int) round(($completedLessons / $course->published_lessons_count) * 100)
            : 0;

        return view('catalog.show', [
            'course' => $course,
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
}
