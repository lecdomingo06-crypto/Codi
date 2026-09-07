<?php

namespace Tests\Unit;

use App\Models\DailyExerciseActivity;
use App\Models\Exercise;
use App\Models\Submission;
use App\Models\User;
use App\Services\StreakService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_day_answers_increase_activity_but_not_streak_length(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();
        $version = $exercise->activeVersion;
        $service = app(StreakService::class);

        $service->recordSubmissionAnswer($user, $this->submission($user, $exercise, 'a'), true, CarbonImmutable::parse('2026-09-07 01:00:00', 'Asia/Manila')->utc());
        $service->recordSubmissionAnswer($user, $this->submission($user, $exercise, 'b'), false, CarbonImmutable::parse('2026-09-07 22:30:00', 'Asia/Manila')->utc());

        $activity = DailyExerciseActivity::firstOrFail();
        $this->assertSame('2026-09-07', substr((string) $activity->activity_date, 0, 10));
        $this->assertSame(2, $activity->answer_count);
        $this->assertSame(1, $activity->accepted_answer_count);
        $this->assertSame(1, $user->streak()->first()->current_streak);
        $this->assertNotNull($version);
    }

    public function test_consecutive_days_increment_and_missed_day_resets_current_streak(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();
        $service = app(StreakService::class);

        $service->recordSubmissionAnswer($user, $this->submission($user, $exercise, 'day-1'), true, CarbonImmutable::parse('2026-09-01 09:00:00', 'Asia/Manila')->utc());
        $service->recordSubmissionAnswer($user, $this->submission($user, $exercise, 'day-2'), true, CarbonImmutable::parse('2026-09-02 09:00:00', 'Asia/Manila')->utc());
        $service->recordSubmissionAnswer($user, $this->submission($user, $exercise, 'day-4'), true, CarbonImmutable::parse('2026-09-04 09:00:00', 'Asia/Manila')->utc());

        $streak = $user->streak()->firstOrFail();
        $this->assertSame(1, $streak->current_streak);
        $this->assertSame(2, $streak->longest_streak);
        $this->assertSame('2026-09-04', substr((string) $streak->last_qualifying_activity_date, 0, 10));
    }

    public function test_duplicate_learning_event_does_not_inflate_counts(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();
        $submission = $this->submission($user, $exercise, 'dupe');
        $service = app(StreakService::class);
        $occurredAt = CarbonImmutable::parse('2026-09-07 08:00:00', 'Asia/Manila')->utc();

        $service->recordSubmissionAnswer($user, $submission, true, $occurredAt);
        $service->recordSubmissionAnswer($user, $submission, true, $occurredAt);

        $this->assertSame(1, DailyExerciseActivity::firstOrFail()->answer_count);
        $this->assertDatabaseCount('learning_events', 1);
    }

    private function submission(User $user, Exercise $exercise, string $key): Submission
    {
        return Submission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'exercise_version_id' => $exercise->active_version_id,
            'idempotency_key' => $key,
            'language' => 'python',
            'source_code' => "def add(a, b):\n    return a + b",
            'time_limit_ms' => 1000,
            'memory_limit_mb' => 128,
            'status' => 'COMPLETED',
            'verdict' => 'ACCEPTED',
            'submitted_at' => now(),
        ]);
    }
}
