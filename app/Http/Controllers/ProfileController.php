<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Concept;
use App\Models\Exercise;
use App\Models\User;
use App\Services\ActivityCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, ActivityCalendarService $calendarService): View
    {
        $user = $request->user()->load('streak');
        $timezone = $user->timezone ?: config('app.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $profileYear = (int) $today->year;
        $calendar = $calendarService->range($user, $today->startOfYear(), $today->endOfYear());
        foreach ($calendar['weeks'] as &$week) {
            foreach ($week as &$day) {
                if ($day) {
                    $day['is_today'] = $day['date'] === $today->toDateString();
                }
            }
        }
        unset($week, $day);
        $calendarActiveDays = collect($calendar['weeks'])
            ->flatten(1)
            ->filter(fn ($day) => $day && $day['answer_count'] > 0)
            ->count();
        $acceptedSubmissions = $user->submissions()->where('verdict', 'ACCEPTED')->count();
        $acceptedExerciseIds = $user->submissions()
            ->where('verdict', 'ACCEPTED')
            ->distinct()
            ->pluck('exercise_id');
        $difficultyCounts = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->selectRaw('difficulty, count(*) as total')
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');
        $solvedDifficultyCounts = Exercise::query()
            ->whereIn('id', $acceptedExerciseIds)
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->selectRaw('difficulty, count(*) as total')
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');
        $totalProblems = (int) $difficultyCounts->sum();
        $solvedProblems = (int) $solvedDifficultyCounts->sum();
        $profileProblemSets = Concept::query()
            ->withCount([
                'exercises as published_exercises_count' => fn ($query) => $query
                    ->where('exercises.publication_status', 'PUBLISHED')
                    ->whereNotNull('exercises.active_version_id'),
                'exercises as solved_exercises_count' => fn ($query) => $query
                    ->where('exercises.publication_status', 'PUBLISHED')
                    ->whereNotNull('exercises.active_version_id')
                    ->whereIn('exercises.id', $acceptedExerciseIds),
            ])
            ->orderByDesc('published_exercises_count')
            ->orderBy('name')
            ->get()
            ->filter(fn (Concept $concept) => $concept->published_exercises_count > 0)
            ->take(6)
            ->values();

        $profileProblemSets->each(function (Concept $concept): void {
            $concept->progress_percent = $concept->published_exercises_count > 0
                ? (int) round(($concept->solved_exercises_count / $concept->published_exercises_count) * 100)
                : 0;
        });

        $recentSubmissions = $user->submissions()->with('exercise')->latest()->limit(4)->get();
        $recentPosts = $user->communityPosts()->latest()->limit(4)->get();
        $rankSummary = $this->problemRankSummary($user, $solvedProblems);

        return view('profile.show', [
            'user' => $user,
            'calendar' => $calendar,
            'profileYear' => $profileYear,
            'calendarActiveDays' => $calendarActiveDays,
            'acceptedSubmissions' => $acceptedSubmissions,
            'difficultyCounts' => $difficultyCounts,
            'solvedDifficultyCounts' => $solvedDifficultyCounts,
            'totalProblems' => $totalProblems,
            'profileProblemSets' => $profileProblemSets,
            'rankSummary' => $rankSummary,
            'submissionCount' => $user->submissions()->count(),
            'communityPostCount' => $user->communityPosts()->count(),
            'communityCommentCount' => $user->communityComments()->count(),
            'recentSubmissions' => $recentSubmissions,
            'recentPosts' => $recentPosts,
            'totalCommunityPosts' => CommunityPost::count(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'timezones' => $this->timezones(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', Rule::in($this->timezones())],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($validated['avatar']);

        $user->update($validated);

        return back()->with('status', 'Profile updated. Historical activity dates were left unchanged.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => $this->passwordRules(),
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('status', 'Password updated.');
    }

    /**
     * @return array{top_percent: float, rank: int, total_users: int, distribution: array<int, array{count: int, height: int, is_current: bool, label: string}>}
     */
    private function problemRankSummary(User $user, int $solvedProblems): array
    {
        $leaderboardRows = User::query()
            ->where(function ($query) use ($user): void {
                $query->where(function ($query): void {
                    $query->where('users.role', 'USER')
                        ->where('users.status', 'ACTIVE');
                })->orWhere('users.id', $user->id);
            })
            ->leftJoin('submissions', function ($join): void {
                $join->on('users.id', '=', 'submissions.user_id')
                    ->where('submissions.verdict', 'ACCEPTED');
            })
            ->leftJoin('exercises', function ($join): void {
                $join->on('exercises.id', '=', 'submissions.exercise_id')
                    ->where('exercises.publication_status', 'PUBLISHED')
                    ->whereNotNull('exercises.active_version_id');
            })
            ->select('users.id')
            ->selectRaw('COUNT(DISTINCT exercises.id) as solved_count')
            ->groupBy('users.id')
            ->get()
            ->map(fn ($row): array => [
                'user_id' => (int) $row->id,
                'solved_count' => (int) $row->solved_count,
            ]);

        $usersAhead = $leaderboardRows
            ->filter(fn (array $row): bool => $row['solved_count'] > $solvedProblems)
            ->count();
        $userRank = $usersAhead + 1;
        $totalRankedUsers = max(1, $leaderboardRows->count());

        return [
            'top_percent' => min(100, max(1, round(($userRank / $totalRankedUsers) * 100, 1))),
            'rank' => $userRank,
            'total_users' => $totalRankedUsers,
            'distribution' => $this->rankDistribution($leaderboardRows->pluck('solved_count'), $solvedProblems),
        ];
    }

    /**
     * @param  Collection<int, int>  $solvedCounts
     * @return array<int, array{count: int, height: int, is_current: bool, label: string}>
     */
    private function rankDistribution(Collection $solvedCounts, int $userSolved): array
    {
        $bucketCount = 6;
        $maxSolved = max(1, (int) $solvedCounts->max());
        $bucketSize = max(1, (int) ceil(($maxSolved + 1) / $bucketCount));
        $buckets = array_fill(0, $bucketCount, 0);

        foreach ($solvedCounts as $solvedCount) {
            $index = min($bucketCount - 1, intdiv((int) $solvedCount, $bucketSize));
            $buckets[$index]++;
        }

        $maxBucket = max(1, max($buckets));
        $currentBucket = min($bucketCount - 1, intdiv($userSolved, $bucketSize));

        return array_map(function (int $count, int $index) use ($maxBucket, $currentBucket, $bucketSize): array {
            $start = $index * $bucketSize;
            $end = $start + $bucketSize - 1;

            return [
                'count' => $count,
                'height' => $count > 0 ? 18 + (int) round(($count / $maxBucket) * 82) : 8,
                'is_current' => $index === $currentBucket,
                'label' => $start === $end ? "{$start} solved" : "{$start}-{$end} solved",
            ];
        }, $buckets, array_keys($buckets));
    }

    /**
     * @return array<int, mixed>
     */
    private function passwordRules(): array
    {
        return [
            'required',
            'confirmed',
            Password::min(8),
            function (string $attribute, mixed $value, \Closure $fail): void {
                $password = (string) $value;
                $variety = (int) preg_match('/[a-z]/', $password)
                    + (int) preg_match('/[A-Z]/', $password)
                    + (int) preg_match('/\d/', $password)
                    + (int) preg_match('/[^A-Za-z0-9]/', $password);
                $lengthScore = strlen($password) >= 12 ? 2 : (strlen($password) >= 8 ? 1 : 0);

                if (strlen($password) < 8 || $lengthScore + $variety < 4) {
                    $fail('The password is too weak. Use at least 8 characters with a mix of uppercase, lowercase, numbers, and symbols.');
                }
            },
        ];
    }

    /**
     * @return array<int, string>
     */
    private function timezones(): array
    {
        return ['UTC', 'Asia/Manila', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London'];
    }
}
