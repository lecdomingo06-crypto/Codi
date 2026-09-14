<?php

namespace App\Services;

use App\Models\DailyExerciseActivity;
use App\Models\LearningEvent;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserStreak;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class StreakService
{
    /**
     * @return array{recorded:bool, first_accepted_today:bool, current_streak:int, longest_streak:int, activity_date:string}
     */
    public function recordSubmissionAnswer(User $user, Submission $submission, bool $accepted, ?CarbonImmutable $occurredAt = null): array
    {
        $occurredAt = $occurredAt ?? CarbonImmutable::now('UTC');

        return DB::transaction(function () use ($user, $submission, $accepted, $occurredAt): array {
            $localDate = $occurredAt
                ->setTimezone($user->timezone ?: 'UTC')
                ->toDateString();

            $event = LearningEvent::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'event_type' => 'answer_submitted',
                    'idempotency_key' => 'submission:'.$submission->id,
                ],
                [
                    'exercise_id' => $submission->exercise_id,
                    'exercise_version_id' => $submission->exercise_version_id,
                    'submission_id' => $submission->id,
                    'metadata' => ['accepted' => $accepted],
                    'occurred_at' => $occurredAt,
                ],
            );

            if (! $event->wasRecentlyCreated) {
                $streak = UserStreak::query()->firstOrCreate(['user_id' => $user->id]);

                return [
                    'recorded' => false,
                    'first_accepted_today' => false,
                    'current_streak' => $streak->current_streak,
                    'longest_streak' => $streak->longest_streak,
                    'activity_date' => $localDate,
                ];
            }

            $activity = DailyExerciseActivity::query()
                ->where('user_id', $user->id)
                ->where('activity_date', $localDate)
                ->lockForUpdate()
                ->first();
            $firstAcceptedToday = $accepted && (! $activity || $activity->accepted_answer_count === 0);

            if ($activity) {
                $activity->increment('answer_count');
                if ($accepted) {
                    $activity->increment('accepted_answer_count');
                }
                $activity->forceFill(['last_answer_at' => $occurredAt])->save();
            } else {
                DailyExerciseActivity::create([
                    'user_id' => $user->id,
                    'activity_date' => $localDate,
                    'answer_count' => 1,
                    'accepted_answer_count' => $accepted ? 1 : 0,
                    'first_answer_at' => $occurredAt,
                    'last_answer_at' => $occurredAt,
                ]);
            }

            $streak = UserStreak::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $streak) {
                $streak = UserStreak::create(['user_id' => $user->id]);
            }

            $lastDate = $streak->getRawOriginal('last_qualifying_activity_date');
            $lastDate = $lastDate ? substr((string) $lastDate, 0, 10) : null;

            if ($lastDate !== $localDate) {
                $yesterday = CarbonImmutable::parse($localDate, $user->timezone ?: 'UTC')->subDay()->toDateString();
                $current = $lastDate === $yesterday ? $streak->current_streak + 1 : 1;

                $streak->forceFill([
                    'current_streak' => $current,
                    'longest_streak' => max($streak->longest_streak, $current),
                    'last_qualifying_activity_date' => $localDate,
                ])->save();
            }

            return [
                'recorded' => true,
                'first_accepted_today' => $firstAcceptedToday,
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'activity_date' => $localDate,
            ];
        });
    }
}
