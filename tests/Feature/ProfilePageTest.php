<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_overview_uses_short_contribution_view(): void
    {
        $this->seed();

        $user = User::factory()->create(['name' => 'Learner One']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Learner One')
            ->assertSee(route('profile.edit'), false)
            ->assertSee('submissions in')
            ->assertSee('Active days')
            ->assertSee('Max streak')
            ->assertSee('Problems')
            ->assertSee(route('catalog.index', ['search' => 'Arrays']), false)
            ->assertSee('Contribution activity')
            ->assertDontSee('Current password')
            ->assertDontSee('Repositories');
    }

    public function test_profile_rank_card_uses_real_problem_solves(): void
    {
        $this->seed();

        $currentUser = User::factory()->create(['name' => 'Middle Learner']);
        $aheadUser = User::factory()->create(['name' => 'Ahead Learner']);
        $behindUser = User::factory()->create(['name' => 'Behind Learner']);
        $exercises = Exercise::query()
            ->where('publication_status', 'PUBLISHED')
            ->whereNotNull('active_version_id')
            ->orderBy('id')
            ->take(3)
            ->get();

        $this->assertCount(3, $exercises);

        $this->acceptedSubmission($aheadUser, $exercises[0], 'ahead-1');
        $this->acceptedSubmission($aheadUser, $exercises[1], 'ahead-2');
        $this->acceptedSubmission($aheadUser, $exercises[2], 'ahead-3');
        $this->acceptedSubmission($currentUser, $exercises[0], 'current-1');
        $this->acceptedSubmission($currentUser, $exercises[1], 'current-2');
        $this->acceptedSubmission($behindUser, $exercises[0], 'behind-1');

        $this->actingAs($currentUser)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Top 50.0%')
            ->assertSee('2 solved')
            ->assertSee('Rank #2 of 4 learners');
    }

    public function test_profile_edit_page_contains_profile_and_password_settings(): void
    {
        $user = User::factory()->create(['name' => 'Learner One']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Back to Profile')
            ->assertSee('Profile Photo')
            ->assertSee('Display Name')
            ->assertSee('Anonymous Username')
            ->assertSee('Change password')
            ->assertSee('GitHub Integration');
    }

    public function test_user_can_update_profile_picture(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Learner One',
            'timezone' => 'Asia/Manila',
        ]);

        $avatar = UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Learner One',
                'timezone' => 'Asia/Manila',
                'avatar' => $avatar,
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('/storage/'.$user->avatar_path, false);
    }

    public function test_user_can_update_password_from_profile(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassw0rd!'),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.password.update'), [
                'current_password' => 'OldPassw0rd!',
                'password' => 'NewPassw0rd!',
                'password_confirmation' => 'NewPassw0rd!',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Password updated.');

        $this->assertTrue(Hash::check('NewPassw0rd!', $user->refresh()->password));
    }

    private function acceptedSubmission(User $user, Exercise $exercise, string $key): void
    {
        Submission::create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'exercise_version_id' => $exercise->active_version_id,
            'idempotency_key' => $key,
            'language' => 'python',
            'runtime_version' => 'local-simulated-judge',
            'time_limit_ms' => 1000,
            'memory_limit_mb' => 128,
            'source_code' => "def solve():\n    return True",
            'status' => 'COMPLETED',
            'verdict' => 'ACCEPTED',
            'submitted_at' => now(),
        ]);
    }
}
