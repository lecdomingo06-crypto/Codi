<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_settings_page_uses_short_contribution_view(): void
    {
        $user = User::factory()->create(['name' => 'Learner One']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Learner One')
            ->assertSee('Profile settings')
            ->assertSee('contributions in the last 12 weeks')
            ->assertSee('Contribution activity')
            ->assertDontSee('Repositories');
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
}
