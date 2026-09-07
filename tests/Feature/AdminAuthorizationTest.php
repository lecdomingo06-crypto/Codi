<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'USER']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_must_login_before_admin_routes(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_publish_an_exercise_with_required_difficulty_and_tests(): void
    {
        $this->seed();
        $admin = User::where('role', 'ADMIN')->firstOrFail();
        $course = Course::firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.exercises.store'), $this->exercisePayload([
                'course_id' => $course->id,
                'slug' => 'multiply-two-numbers',
                'title' => 'Multiply Two Numbers',
                'summary' => 'Return the product of two integers.',
                'difficulty' => 'MEDIUM',
            ]))
            ->assertRedirect();

        $exercise = Exercise::where('slug', 'multiply-two-numbers')->firstOrFail();
        $version = $exercise->latestVersion()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.exercises.publish', [$exercise, $version]))
            ->assertRedirect(route('admin.exercises.index'));

        $this->assertSame('PUBLISHED', $exercise->refresh()->publication_status);
        $this->assertSame('MEDIUM', $exercise->difficulty);
        $this->assertSame($version->id, $exercise->active_version_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'exercise.published',
            'auditable_id' => $exercise->id,
        ]);
    }

    public function test_admin_cannot_create_exercise_without_allowed_difficulty(): void
    {
        $this->seed();
        $admin = User::where('role', 'ADMIN')->firstOrFail();
        $course = Course::firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.exercises.create'))
            ->post(route('admin.exercises.store'), $this->exercisePayload([
                'course_id' => $course->id,
                'difficulty' => '',
            ]))
            ->assertRedirect(route('admin.exercises.create'))
            ->assertSessionHasErrors('difficulty');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function exercisePayload(array $overrides = []): array
    {
        return array_merge([
            'course_id' => null,
            'lesson_id' => null,
            'title' => 'Sum Two Numbers Again',
            'slug' => 'sum-two-numbers-again',
            'summary' => 'Return the sum of two integers.',
            'description_markdown' => 'Return `a + b`.',
            'difficulty' => 'EASY',
            'concept_tags' => 'functions, arithmetic',
            'constraints' => '-100 <= a, b <= 100',
            'supported_languages' => ['python', 'javascript'],
            'starter_code_python' => "def add(a, b):\n    pass",
            'starter_code_javascript' => "function add(a, b) {\n}",
            'starter_code_typescript' => '',
            'function_signature_python' => 'def add(a, b):',
            'function_signature_javascript' => 'function add(a, b)',
            'function_signature_typescript' => '',
            'official_solution_python' => "def add(a, b):\n    return a + b",
            'official_solution_javascript' => "function add(a, b) {\n  return a + b;\n}",
            'official_solution_typescript' => '',
            'visible_examples_json' => '[]',
            'visible_tests_json' => '[{"name":"sample","input":"{\"a\":2,\"b\":3}","expected_output":"5"}]',
            'hidden_tests_json' => '[{"name":"hidden","input":"{\"a\":10,\"b\":-4}","expected_output":"6"}]',
            'hints_text' => 'Use addition.',
            'explanation_markdown' => 'Addition combines the two numbers.',
            'time_limit_ms' => 1000,
            'memory_limit_mb' => 128,
            'points' => 10,
        ], $overrides);
    }
}
