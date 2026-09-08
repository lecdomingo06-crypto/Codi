<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Exercise;
use App\Models\Submission;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(): View
    {
        $start = now()->subDays(6)->startOfDay();
        $submissions = Submission::query()->where('submitted_at', '>=', $start);
        $weeklySubmissionCount = (clone $submissions)->count();
        $acceptedSubmissionCount = (clone $submissions)->where('verdict', 'ACCEPTED')->count();

        $dailyTotals = (clone $submissions)->selectRaw('DATE(submitted_at) as day, COUNT(*) as total')->groupBy('day')->pluck('total', 'day');
        $activity = collect(range(0, 6))->map(function (int $offset) use ($dailyTotals): array {
            $date = now()->subDays(6 - $offset)->startOfDay();

            return ['label' => $date->format('D'), 'date' => $date->format('M j'), 'total' => (int) ($dailyTotals[$date->toDateString()] ?? 0)];
        });

        $topExercises = Exercise::query()
            ->whereHas('submissions', fn ($query) => $query->where('submitted_at', '>=', $start))
            ->withCount(['submissions as submission_count' => fn ($query) => $query->where('submitted_at', '>=', $start)])
            ->withCount(['submissions as accepted_submission_count' => fn ($query) => $query->where('submitted_at', '>=', $start)->where('verdict', 'ACCEPTED')])
            ->orderByDesc('submission_count')->limit(5)->get();

        return view('admin.analytics.index', [
            'weeklySubmissionCount' => $weeklySubmissionCount,
            'activeLearnerCount' => (clone $submissions)->distinct('user_id')->count('user_id'),
            'newEnrollmentCount' => Enrollment::where('enrolled_at', '>=', $start)->count(),
            'acceptanceRate' => $weeklySubmissionCount === 0 ? 0 : (int) round(($acceptedSubmissionCount / $weeklySubmissionCount) * 100),
            'activity' => $activity, 'maxDailyTotal' => max(1, $activity->max('total')), 'topExercises' => $topExercises,
        ]);
    }
}
