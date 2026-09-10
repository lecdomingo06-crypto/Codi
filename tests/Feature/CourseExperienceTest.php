<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_and_course_progress_uses_completed_lessons(): void
    {
        $student = User::factory()->create(['role' => 'USER']);
        $course = Course::create([
            'title' => 'Course Progress Test',
            'slug' => 'course-progress-test',
            'summary' => 'Test course',
            'category' => 'Programming',
            'difficulty' => 'EASY',
            'publication_status' => 'PUBLISHED',
            'published_at' => now(),
        ]);
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Core module',
            'slug' => 'core-module',
            'sort_order' => 1,
        ]);
        $lessons = collect(range(1, 2))->map(fn (int $order) => Lesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Lesson '.$order,
            'slug' => 'progress-lesson-'.$order,
            'summary' => 'Test lesson',
            'body_markdown' => 'Test body',
            'sort_order' => $order,
            'publication_status' => 'PUBLISHED',
            'published_at' => now(),
        ]));

        $this->actingAs($student)
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee('0%')
            ->assertSee('0 / 2 lessons completed');

        $this->actingAs($student)
            ->post(route('courses.enroll', $course))
            ->assertRedirect();

        $this->actingAs($student)
            ->post(route('lessons.complete', $lessons->first()))
            ->assertRedirect();

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('50%')
            ->assertSee('1 / 2 lessons completed')
            ->assertSee('Core module');
    }

    public function test_admin_sees_management_actions_and_cannot_enroll_or_complete(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $course = Course::create([
            'title' => 'Admin Course Test',
            'slug' => 'admin-course-test',
            'summary' => 'Test course',
            'category' => 'Programming',
            'difficulty' => 'EASY',
            'publication_status' => 'PUBLISHED',
            'published_at' => now(),
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Admin lesson',
            'slug' => 'admin-lesson-test',
            'summary' => 'Test lesson',
            'body_markdown' => 'Test body',
            'sort_order' => 1,
            'publication_status' => 'PUBLISHED',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertDontSee('Manage modules')
            ->assertDontSee('Manage lessons')
            ->assertDontSee('Manage exercises')
            ->assertDontSee('Course progress')
            ->assertDontSee('Edit course')
            ->assertDontSee('Enroll in course');

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('Edit')
            ->assertSee($course->title);

        $this->actingAs($admin)
            ->post(route('courses.enroll', $course))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('lessons.complete', $lesson))
            ->assertForbidden();

        $this->assertSame(0, DB::table('lesson_progress')->count());
    }
}
