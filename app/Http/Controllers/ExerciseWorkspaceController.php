<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Run;
use App\Models\Submission;
use App\Services\JudgeService;
use App\Services\StreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExerciseWorkspaceController extends Controller
{
    public function show(Request $request, Exercise $exercise): View
    {
        $version = $this->publishedVersion($exercise);
        $version->load('testBundle.visibleTestCases');

        $lastRun = null;
        if ($runId = session('last_run_id')) {
            $lastRun = Run::query()
                ->where('user_id', $request->user()->id)
                ->with('testResults')
                ->find($runId);
        }

        return view('exercises.show', [
            'exercise' => $exercise->load('concepts'),
            'version' => $version,
            'visibleTests' => $version->testBundle?->visibleTestCases ?? collect(),
            'starterCode' => $version->starterCodeFor($version->supported_languages[0] ?? 'python'),
            'lastRun' => $lastRun,
            'submissions' => $request->user()->submissions()
                ->where('exercise_id', $exercise->id)
                ->with('testResults')
                ->latest()
                ->limit(8)
                ->get(),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function run(Request $request, Exercise $exercise, JudgeService $judge): RedirectResponse|JsonResponse
    {
        $version = $this->publishedVersion($exercise);
        $validated = $this->validateAttempt($request, $version->supported_languages);

        $testCases = $version->testBundle()->firstOrFail()->visibleTestCases()->get();
        $result = $judge->evaluate($version, $validated['language'], $validated['source_code'], $testCases);

        $run = Run::create([
            'user_id' => $request->user()->id,
            'exercise_id' => $exercise->id,
            'exercise_version_id' => $version->id,
            'language' => $validated['language'],
            'source_code' => $validated['source_code'],
            'status' => 'COMPLETED',
            'verdict' => $result['verdict'],
            'execution_time_ms' => $result['execution_time_ms'],
            'memory_kb' => $result['memory_kb'],
            'output' => $result['output'],
        ]);

        $this->storeTestResults($run, null, $result['tests']);

        if ($request->wantsJson()) {
            $run->load('testResults');

            return response()->json([
                'id' => $run->id,
                'verdict' => $run->verdict,
                'execution_time_ms' => $run->execution_time_ms,
                'memory_kb' => $run->memory_kb,
                'output' => $run->output,
                'tests' => $run->testResults->map(fn ($test) => [
                    'test_name' => $test->test_name,
                    'visibility' => $test->visibility,
                    'verdict' => $test->verdict,
                    'execution_time_ms' => $test->execution_time_ms,
                    'memory_kb' => $test->memory_kb,
                    'message' => $test->message,
                ])->values(),
            ]);
        }

        return redirect()->route('exercises.show', $exercise)
            ->with('last_run_id', $run->id)
            ->withInput($validated);
    }

    public function check(Request $request, Exercise $exercise, JudgeService $judge): JsonResponse
    {
        $version = $this->publishedVersion($exercise);
        $validated = $this->validateAttempt($request, $version->supported_languages);
        $testCases = $version->testBundle()->firstOrFail()->visibleTestCases()->get();

        return response()->json($judge->evaluate(
            $version,
            $validated['language'],
            $validated['source_code'],
            $testCases,
        ));
    }

    public function submit(Request $request, Exercise $exercise, JudgeService $judge, StreakService $streaks): RedirectResponse|JsonResponse
    {
        $version = $this->publishedVersion($exercise);
        $validated = $this->validateAttempt($request, $version->supported_languages) + $request->validate([
            'idempotency_key' => ['required', 'string', 'max:96'],
        ]);

        $existing = Submission::query()
            ->where('user_id', $request->user()->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing) {
            return $request->wantsJson()
                ? response()->json($existing->load('testResults'))
                : redirect()->route('submissions.show', $existing)->with('status', 'Duplicate submission key reused; existing result returned.');
        }

        $alreadyAccepted = Submission::query()
            ->where('user_id', $request->user()->id)
            ->where('exercise_id', $exercise->id)
            ->where('verdict', 'ACCEPTED')
            ->exists();

        $submission = DB::transaction(function () use ($request, $exercise, $version, $validated) {
            return Submission::create([
                'user_id' => $request->user()->id,
                'exercise_id' => $exercise->id,
                'exercise_version_id' => $version->id,
                'idempotency_key' => $validated['idempotency_key'],
                'language' => $validated['language'],
                'source_code' => $validated['source_code'],
                'time_limit_ms' => $version->time_limit_ms,
                'memory_limit_mb' => $version->memory_limit_mb,
                'status' => 'QUEUED',
                'submitted_at' => now(),
            ]);
        });

        $testCases = $version->testBundle()->firstOrFail()->testCases()->get();
        $result = $judge->evaluate($version, $validated['language'], $validated['source_code'], $testCases);

        $submission->forceFill([
            'status' => 'COMPLETED',
            'verdict' => $result['verdict'],
            'execution_time_ms' => $result['execution_time_ms'],
            'memory_kb' => $result['memory_kb'],
        ])->save();

        $this->storeTestResults(null, $submission, $result['tests']);
        $accepted = $result['verdict'] === 'ACCEPTED';
        $streaks->recordSubmissionAnswer($request->user(), $submission, $accepted);

        if ($accepted && ! $alreadyAccepted) {
            $request->user()->increment('points', $version->points);
        }

        if ($request->wantsJson()) {
            return response()->json($submission->load('testResults'), 201);
        }

        return redirect()->route('submissions.show', $submission);
    }

    public function submission(Request $request, Submission $submission): View|JsonResponse
    {
        abort_unless($submission->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $submission->load('exercise', 'exerciseVersion', 'testResults');

        if ($request->wantsJson()) {
            return response()->json($submission);
        }

        return view('exercises.submission', ['submission' => $submission]);
    }

    private function publishedVersion(Exercise $exercise)
    {
        abort_unless($exercise->publication_status === 'PUBLISHED' && $exercise->activeVersion, 404);

        return $exercise->activeVersion;
    }

    /**
     * @param  array<int, string>  $languages
     * @return array{language:string, source_code:string}
     */
    private function validateAttempt(Request $request, array $languages): array
    {
        return $request->validate([
            'language' => ['required', Rule::in($languages)],
            'source_code' => ['required', 'string', 'max:20000'],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tests
     */
    private function storeTestResults(?Run $run, ?Submission $submission, array $tests): void
    {
        foreach ($tests as $test) {
            $payload = $test + [
                'run_id' => $run?->id,
                'submission_id' => $submission?->id,
            ];

            \App\Models\TestResult::create($payload);
        }
    }
}
