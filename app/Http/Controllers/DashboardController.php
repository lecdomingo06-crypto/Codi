<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exercise;
use App\Services\ActivityCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ActivityCalendarService $calendarService): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $acceptedExerciseIds = $user->submissions()
            ->where('verdict', 'ACCEPTED')
            ->pluck('exercise_id');

        $recommendation = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->whereNotIn('id', $acceptedExerciseIds)
            ->orderByRaw("CASE difficulty WHEN 'EASY' THEN 1 WHEN 'MEDIUM' THEN 2 WHEN 'HARD' THEN 3 ELSE 4 END")
            ->first();

        return view('dashboard', [
            'user' => $user->load('streak'),
            'enrollments' => $user->enrollments()->with('course')->latest()->get(),
            'availableCourses' => Course::where('publication_status', 'PUBLISHED')->count(),
            'recentSubmissions' => $user->submissions()->with('exercise')->latest()->limit(5)->get(),
            'recommendation' => $recommendation,
            'calendar' => $calendarService->yearlyCalendar($user),
        ]);
    }
}
