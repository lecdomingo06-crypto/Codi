<?php

namespace Tests\Unit;

use App\Models\DailyExerciseActivity;
use App\Models\User;
use App\Services\ActivityCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_reports_totals_intensity_and_today_marker(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Manila']);

        DailyExerciseActivity::create([
            'user_id' => $user->id,
            'activity_date' => '2026-09-05',
            'answer_count' => 1,
            'accepted_answer_count' => 1,
            'first_answer_at' => now(),
            'last_answer_at' => now(),
        ]);

        DailyExerciseActivity::create([
            'user_id' => $user->id,
            'activity_date' => '2026-09-07',
            'answer_count' => 6,
            'accepted_answer_count' => 3,
            'first_answer_at' => now(),
            'last_answer_at' => now(),
        ]);

        $calendar = app(ActivityCalendarService::class)->yearlyCalendar(
            $user,
            CarbonImmutable::parse('2026-09-07', 'Asia/Manila'),
        );

        $days = collect($calendar['weeks'])->flatten(1)->filter();
        $today = $days->firstWhere('date', '2026-09-07');

        $this->assertSame(7, $calendar['total_answers']);
        $this->assertSame(4, $today['intensity']);
        $this->assertTrue($today['is_today']);
        $this->assertSame('7 exercise answers in the last year', $calendar['summary']);
    }
}
