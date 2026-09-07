<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiddenContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_open_draft_exercise(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $draft = Exercise::create([
            'title' => 'Draft Only',
            'slug' => 'draft-only',
            'summary' => 'Not published.',
            'difficulty' => 'EASY',
            'publication_status' => 'DRAFT',
        ]);

        $this->actingAs($user)
            ->get(route('exercises.show', $draft))
            ->assertNotFound();
    }

    public function test_hidden_tests_are_not_rendered_in_workspace_or_submission(): void
    {
        $this->seed();
        $user = User::where('role', 'USER')->firstOrFail();
        $exercise = Exercise::where('slug', 'sum-two-numbers')->firstOrFail();

        $this->actingAs($user)
            ->get(route('exercises.show', $exercise))
            ->assertOk()
            ->assertSee('sample positive integers')
            ->assertDontSee('hidden lower bound')
            ->assertDontSee('{"a":-10000,"b":1}', false);

        $this->actingAs($user)
            ->post(route('exercises.submit', $exercise), [
                'idempotency_key' => 'privacy-key',
                'language' => 'python',
                'source_code' => "def add(a, b):\n    return a + b",
            ])
            ->assertRedirect();

        $submission = $user->submissions()->firstOrFail();

        $this->actingAs($user)
            ->get(route('submissions.show', $submission))
            ->assertOk()
            ->assertSee('HIDDEN')
            ->assertDontSee('{"a":-10000,"b":1}', false)
            ->assertDontSee('-9999');
    }
}
