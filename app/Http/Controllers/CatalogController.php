<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
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
            'exercises' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->with('concepts'),
        ]);

        return view('catalog.show', ['course' => $course]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->publication_status === 'PUBLISHED', 404);

        Enrollment::firstOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            ['enrolled_at' => now()],
        );

        return back()->with('status', 'Enrollment saved.');
    }
}
