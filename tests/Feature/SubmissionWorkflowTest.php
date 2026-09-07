<?php

namespace Tests\Feature;

use App\Models\DailyExerciseActivity;
use App\Models\Exercise;
use App\Models\Run;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_does_not_count_as_daily_answer_but_submit_does(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();

        $this->actingAs($user)
            ->post(route('exercises.run', $exercise), [
                'language' => 'python',
                'source_code' => "def add(a, b):\n    return a + b",
            ])
            ->assertRedirect(route('exercises.show', $exercise));

        $this->assertSame(1, Run::count());
        $this->assertSame(0, DailyExerciseActivity::count());

        $this->actingAs($user)
            ->post(route('exercises.submit', $exercise), [
                'idempotency_key' => 'submit-once',
                'language' => 'python',
                'source_code' => "def add(a, b):\n    return a + b",
            ])
            ->assertRedirect();

        $this->assertSame(1, Submission::count());
        $this->assertSame('ACCEPTED', Submission::first()->verdict);
        $this->assertSame(1, DailyExerciseActivity::first()->answer_count);
        $this->assertSame(1, DailyExerciseActivity::first()->accepted_answer_count);
    }

    public function test_ajax_run_returns_visible_test_results_without_daily_activity(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('exercises.run', $exercise), [
                'language' => 'python',
                'source_code' => "def add(a, b):\n    return a + b",
            ])
            ->assertOk()
            ->assertJsonPath('verdict', 'ACCEPTED')
            ->assertJsonFragment(['visibility' => 'VISIBLE'])
            ->assertJsonMissing(['visibility' => 'HIDDEN']);

        $this->assertSame(1, Run::count());
        $this->assertSame(0, Submission::count());
        $this->assertSame(0, DailyExerciseActivity::count());
    }

    public function test_auto_check_uses_visible_tests_without_persisting_activity_or_runs(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('exercises.check', $exercise), [
                'language' => 'python',
                'source_code' => "def add(a, b):\n    return a + b",
            ])
            ->assertOk()
            ->assertJsonPath('verdict', 'ACCEPTED')
            ->assertJsonFragment(['visibility' => 'VISIBLE'])
            ->assertJsonMissing(['visibility' => 'HIDDEN']);

        $this->assertSame(0, Run::count());
        $this->assertSame(0, Submission::count());
        $this->assertSame(0, DailyExerciseActivity::count());
        $this->assertSame(0, $user->streak()->first()->current_streak);
    }

    public function test_reusing_submission_idempotency_key_returns_existing_submission_without_inflating_activity(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();
        $payload = [
            'idempotency_key' => 'same-key',
            'language' => 'python',
            'source_code' => "def add(a, b):\n    return a + b",
        ];

        $this->actingAs($user)->post(route('exercises.submit', $exercise), $payload);
        $this->actingAs($user)->post(route('exercises.submit', $exercise), $payload);

        $this->assertSame(1, Submission::count());
        $this->assertSame(1, DailyExerciseActivity::first()->answer_count);
        $this->assertSame(1, $user->streak()->first()->current_streak);
    }
}
