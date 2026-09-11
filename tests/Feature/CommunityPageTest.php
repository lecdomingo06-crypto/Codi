<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_image_post_on_community_feed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('community.store'), [
                'title' => 'Why is my two sum code failing?',
                'body' => 'I attached the error from my local run.',
                'tag' => 'HELP',
                'image' => UploadedFile::fake()->createWithContent(
                    'error.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
                ),
            ])
            ->assertRedirect(route('community.index'));

        $post = CommunityPost::firstOrFail();

        $this->assertSame('Why is my two sum code failing?', $post->title);
        $this->assertSame('HELP', $post->tag);
        $this->assertNotNull($post->image_path);
        Storage::disk('public')->assertExists($post->image_path);

        $this->actingAs($user)
            ->get(route('community.index'))
            ->assertOk()
            ->assertSee('/storage/community/', false);
    }

    public function test_user_can_comment_and_vote_on_post(): void
    {
        $user = User::factory()->create();
        $post = CommunityPost::create([
            'user_id' => $user->id,
            'title' => 'Share your recursion tips',
            'body' => 'What helped recursion click for you?',
            'tag' => 'QUESTION',
        ]);

        $this->actingAs($user)
            ->post(route('community.comments.store', $post), [
                'body' => 'Draw the call stack first.',
            ])
            ->assertRedirect(route('community.index').'#post-'.$post->id);

        $this->assertDatabaseHas('community_comments', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Draw the call stack first.',
        ]);

        $this->actingAs($user)
            ->post(route('community.vote', $post), ['value' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('community_votes', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'value' => 1,
        ]);
    }

    public function test_user_can_filter_to_my_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        CommunityPost::create([
            'user_id' => $user->id,
            'title' => 'My dynamic programming question',
            'body' => 'How do I memoize this?',
            'tag' => 'QUESTION',
        ]);

        CommunityPost::create([
            'user_id' => $otherUser->id,
            'title' => 'Someone else post',
            'body' => 'This should not be shown in my posts.',
            'tag' => 'SHOWCASE',
        ]);

        $this->actingAs($user)
            ->get(route('community.index', ['filter' => 'MINE']))
            ->assertOk()
            ->assertSee('My dynamic programming question')
            ->assertDontSee('Someone else post');
    }

    public function test_owner_can_delete_post_and_uploaded_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Storage::disk('public')->put('community/error.png', 'fake image');

        $post = CommunityPost::create([
            'user_id' => $user->id,
            'title' => 'Delete this screenshot',
            'body' => 'This post has an image.',
            'tag' => 'BUG',
            'image_path' => 'community/error.png',
        ]);

        $this->actingAs($user)
            ->delete(route('community.destroy', $post))
            ->assertRedirect(route('community.index', ['filter' => 'MINE']));

        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing('community/error.png');
    }
}
