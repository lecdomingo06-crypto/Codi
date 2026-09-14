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
            ->assertSee('/storage/community/', false)
            ->assertSee('class="post-image post-image-button"', false)
            ->assertSee(route('community.users.show', $user), false)
            ->assertSee('data-community-thread-open="'.$post->id.'"', false);
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
            ->assertRedirect(route('community.index', ['thread' => $post->id]).'#post-'.$post->id);

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

    public function test_user_can_vote_without_page_reload(): void
    {
        $user = User::factory()->create();
        $post = CommunityPost::create([
            'user_id' => $user->id,
            'title' => 'Vote without jumping',
            'body' => 'Voting should update in place.',
            'tag' => 'QUESTION',
        ]);

        $this->actingAs($user)
            ->postJson(route('community.vote', $post), ['value' => 1])
            ->assertOk()
            ->assertJson([
                'post_id' => $post->id,
                'score' => 1,
                'user_vote' => 1,
            ]);

        $this->actingAs($user)
            ->postJson(route('community.vote', $post), ['value' => 1])
            ->assertOk()
            ->assertJson([
                'post_id' => $post->id,
                'score' => 0,
                'user_vote' => 0,
            ]);
    }

    public function test_community_comments_open_in_thread_modal(): void
    {
        $user = User::factory()->create(['name' => 'Jeric Giang']);
        $commenter = User::factory()->create(['name' => 'Anonymous participant 590']);

        $post = CommunityPost::create([
            'user_id' => $user->id,
            'title' => 'Need help with arrays',
            'body' => 'How do I compare both ends cleanly?',
            'tag' => 'HELP',
        ]);

        $post->comments()->create([
            'user_id' => $commenter->id,
            'body' => 'Try using left and right pointers.',
        ]);

        $this->actingAs($user)
            ->get(route('community.index', ['thread' => $post->id]))
            ->assertOk()
            ->assertSee('data-community-thread-open="'.$post->id.'"', false)
            ->assertSee('data-community-share-open="'.$post->id.'"', false)
            ->assertSee('id="community-share-'.$post->id.'"', false)
            ->assertSee(route('community.users.show', $user), false)
            ->assertSee(route('community.users.show', $commenter), false)
            ->assertSeeText("Jeric Giang's Post", false)
            ->assertSee('Most relevant')
            ->assertSee('Try using left and right pointers.')
            ->assertSee('placeholder="Comment as Jeric Giang"', false)
            ->assertSee('Say something about this...', false)
            ->assertSee('Share to Community')
            ->assertSee('Copy link')
            ->assertSee('window.CODDY_OPEN_COMMUNITY_THREAD', false)
            ->assertDontSee('class="thread-action-row"', false)
            ->assertDontSee('class="post-comments"', false);
    }

    public function test_user_can_view_community_profile_posts(): void
    {
        $viewer = User::factory()->create();
        $profileUser = User::factory()->create(['name' => 'King Lee']);
        $otherUser = User::factory()->create(['name' => 'Other Learner']);

        $profilePost = CommunityPost::create([
            'user_id' => $profileUser->id,
            'title' => 'King Lee first community post',
            'body' => 'This should appear on King Lee profile.',
            'tag' => 'SHOWCASE',
            'image_path' => 'community/profile-post.png',
        ]);

        CommunityPost::create([
            'user_id' => $otherUser->id,
            'title' => 'Other user post',
            'body' => 'This should not appear on King Lee profile.',
            'tag' => 'HELP',
        ]);

        $this->actingAs($viewer)
            ->get(route('community.users.show', $profileUser))
            ->assertOk()
            ->assertSee('King Lee')
            ->assertSee('Personal details')
            ->assertSee('King Lee first community post')
            ->assertSee('<div class="community-profile-cover" aria-hidden="true"></div>', false)
            ->assertSee('class="community-profile-post-image community-profile-post-image-button"', false)
            ->assertSee('data-community-thread-open="'.$profilePost->id.'"', false)
            ->assertSee('id="community-thread-'.$profilePost->id.'"', false)
            ->assertSee('name="return_to" value="community-user"', false)
            ->assertSee('View discussion')
            ->assertDontSee('href="'.route('community.index', ['thread' => $profilePost->id]).'#post-'.$profilePost->id.'"', false)
            ->assertDontSee('Other user post');

        $this->actingAs($viewer)
            ->post(route('community.comments.store', $profilePost), [
                'body' => 'This profile modal comment stays here.',
                'return_to' => 'community-user',
                'profile_user_id' => $profileUser->id,
            ])
            ->assertRedirect(route('community.users.show', [
                'user' => $profileUser->id,
                'thread' => $profilePost->id,
            ]).'#post-'.$profilePost->id);
    }

    public function test_user_can_share_post_to_community_feed(): void
    {
        $author = User::factory()->create(['name' => 'Original Author']);
        $sharer = User::factory()->create(['name' => 'Learner']);

        $post = CommunityPost::create([
            'user_id' => $author->id,
            'title' => 'AI is Already Better at Coding Than Most Software Developers',
            'body' => 'This post is worth discussing with the group.',
            'tag' => 'QUESTION',
        ]);

        $response = $this->actingAs($sharer)
            ->post(route('community.share', $post), [
                'body' => 'This helped me think about architecture differently.',
            ]);

        $sharedPost = CommunityPost::where('shared_post_id', $post->id)->firstOrFail();

        $response->assertRedirect(route('community.index', ['filter' => 'NEW', 'thread' => $sharedPost->id]).'#post-'.$sharedPost->id);

        $this->assertSame($sharer->id, $sharedPost->user_id);
        $this->assertSame('SHOWCASE', $sharedPost->tag);
        $this->assertSame('This helped me think about architecture differently.', $sharedPost->body);
        $this->assertStringStartsWith('Shared: AI is Already Better', $sharedPost->title);

        $this->actingAs($sharer)
            ->get(route('community.index', ['filter' => 'NEW']))
            ->assertOk()
            ->assertSee('Shared from Original Author')
            ->assertSee('AI is Already Better at Coding Than Most Software Developers')
            ->assertSee('This helped me think about architecture differently.');
    }

    public function test_deleting_shared_post_keeps_original_post(): void
    {
        $author = User::factory()->create(['name' => 'Original Author']);
        $sharer = User::factory()->create(['name' => 'Learner']);

        $originalPost = CommunityPost::create([
            'user_id' => $author->id,
            'title' => 'Original architecture discussion',
            'body' => 'This post belongs to someone else.',
            'tag' => 'QUESTION',
        ]);

        $sharedPost = CommunityPost::create([
            'user_id' => $sharer->id,
            'title' => 'Shared: Original architecture discussion',
            'body' => 'Sharing this with the community.',
            'tag' => 'SHOWCASE',
            'shared_post_id' => $originalPost->id,
        ]);

        $this->actingAs($sharer)
            ->delete(route('community.destroy', $sharedPost), [
                'filter' => 'MINE',
            ])
            ->assertRedirect(route('community.index', ['thread' => $originalPost->id]).'#post-'.$originalPost->id)
            ->assertSessionHas('status', 'Shared post deleted. Original post was kept.');

        $this->assertDatabaseMissing('community_posts', ['id' => $sharedPost->id]);
        $this->assertDatabaseHas('community_posts', [
            'id' => $originalPost->id,
            'user_id' => $author->id,
            'title' => 'Original architecture discussion',
        ]);

        $this->actingAs($sharer)
            ->delete(route('community.destroy', $originalPost))
            ->assertForbidden();
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

    public function test_community_uses_navbar_post_search_instead_of_sidebar_search_card(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('community.index'))
            ->assertOk()
            ->assertSee('placeholder="Search posts"', false)
            ->assertDontSee('community-search-card');
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
