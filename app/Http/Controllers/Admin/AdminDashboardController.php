<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Submission;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'courseCount' => Course::count(),
            'publishedExerciseCount' => Exercise::where('publication_status', 'PUBLISHED')->count(),
            'draftExerciseCount' => Exercise::where('publication_status', 'DRAFT')->count(),
            'submissionCount' => Submission::count(),
            'userCount' => User::where('role', 'USER')->count(),
            'recentSubmissions' => Submission::with('exercise')->latest()->limit(10)->get(),
        ]);
    }
}
