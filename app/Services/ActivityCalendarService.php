<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class ActivityCalendarService
{
    /**
     * @return array{total_answers:int, total_accepted:int, from:string, to:string, weeks:array<int, array<int, array<string, mixed>|null>>, months:array<int, string|null>, summary:string}
     */
    public function yearlyCalendar(User $user, ?CarbonImmutable $today = null): array
    {
        $timezone = $user->timezone ?: 'UTC';
        $today = ($today ?? CarbonImmutable::now($timezone))->setTimezone($timezone)->startOfDay();
        $from = $today->subDays(364);

        return $this->range($user, $from, $today);
    }

    /**
     * @return array{total_answers:int, total_accepted:int, from:string, to:string, weeks:array<int, array<int, array<string, mixed>|null>>, months:array<int, string|null>, summary:string}
     */
    public function range(User $user, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $from = $from->startOfDay();
        $to = $to->startOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $activities = $user->dailyActivities()
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn ($activity) => substr((string) $activity->getRawOriginal('activity_date'), 0, 10));

        $gridStart = $from->startOfWeek(CarbonInterface::SUNDAY);
        $weeks = [];
        $months = [];
        $totalAnswers = 0;
        $totalAccepted = 0;
        $lastMonth = null;

        for ($cursor = $gridStart; $cursor->lessThanOrEqualTo($to); $cursor = $cursor->addWeek()) {
            $week = [];
            $monthLabel = null;

            for ($weekday = 0; $weekday < 7; $weekday++) {
                $date = $cursor->addDays($weekday);

                if ($date->lessThan($from) || $date->greaterThan($to)) {
                    $week[] = null;
                    continue;
                }

                $key = $date->toDateString();
                $activity = $activities->get($key);
                $answerCount = (int) ($activity?->answer_count ?? 0);
                $acceptedCount = (int) ($activity?->accepted_answer_count ?? 0);
                $totalAnswers += $answerCount;
                $totalAccepted += $acceptedCount;

                if ($date->month !== $lastMonth) {
                    $monthLabel = $date->format('M');
                    $lastMonth = $date->month;
                }

                $week[] = [
                    'date' => $key,
                    'label' => $date->format('M j, Y'),
                    'answer_count' => $answerCount,
                    'accepted_answer_count' => $acceptedCount,
                    'intensity' => min($answerCount, 4),
                    'is_today' => $date->isSameDay($to),
                ];
            }

            $weeks[] = $week;
            $months[] = $monthLabel;
        }

        return [
            'total_answers' => $totalAnswers,
            'total_accepted' => $totalAccepted,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'weeks' => $weeks,
            'months' => $months,
            'summary' => "{$totalAnswers} exercise answers in the last year",
        ];
    }
}
