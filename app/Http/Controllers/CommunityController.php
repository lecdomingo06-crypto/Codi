<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $filter = strtoupper((string) $request->query('filter', 'HOT'));
        $tag = strtoupper((string) $request->query('tag', ''));
        $search = trim((string) $request->query('search', ''));

        $posts = CommunityPost::query()
            ->with('user', 'comments.user', 'sharedPost.user')
            ->withCount('comments', 'shares')
            ->withSum('votes as vote_score', 'value')
            ->when($filter === 'MINE', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when(in_array($tag, CommunityPost::TAGS, true), fn ($query) => $query->where('tag', $tag))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->when($filter === 'NEW', fn ($query) => $query->latest())
            ->when($filter !== 'NEW', fn ($query) => $query
                ->orderByDesc('vote_score')
                ->orderByDesc('comments_count')
                ->latest())
            ->get();

        $userVotes = $request->user()
            ->communityVotes()
            ->whereIn('community_post_id', $posts->pluck('id'))
            ->pluck('value', 'community_post_id');

        $tagCounts = CommunityPost::query()
            ->selectRaw('tag, count(*) as total')
            ->groupBy('tag')
            ->pluck('total', 'tag');

        return view('community.index', [
            'posts' => $posts,
            'filter' => $filter,
            'tag' => $tag,
            'search' => $search,
            'userVotes' => $userVotes,
            'tagCounts' => $tagCounts,
        ]);
    }

    public function user(User $user): View
    {
        $posts = CommunityPost::query()
            ->where('user_id', $user->id)
            ->with('user', 'comments.user', 'sharedPost.user')
            ->withCount('comments', 'shares')
            ->withSum('votes as vote_score', 'value')
            ->latest()
            ->get();

        return view('community.user', [
            'profileUser' => $user,
            'posts' => $posts,
            'postCount' => $posts->count(),
            'commentCount' => $user->communityComments()->count(),
            'shareCount' => $user->communityPosts()->whereNotNull('shared_post_id')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'tag' => ['required', Rule::in(CommunityPost::TAGS)],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('community', 'public');
        }

        unset($data['image']);

        $request->user()->communityPosts()->create($data);

        return redirect()
            ->route('community.index')
            ->with('status', 'Post shared with the community.');
    }

    public function comment(Request $request, CommunityPost $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $redirectParams = array_filter([
            'filter' => $request->input('filter'),
            'tag' => $request->input('tag'),
            'search' => $request->input('search'),
            'thread' => $post->id,
        ], fn ($value) => filled($value));

        if ($request->input('return_to') === 'community-user' && $request->integer('profile_user_id') > 0) {
            return redirect()
                ->to(route('community.users.show', [
                    'user' => $request->integer('profile_user_id'),
                    'thread' => $post->id,
                ]).'#post-'.$post->id)
                ->with('status', 'Comment added.');
        }

        return redirect()
            ->to(route('community.index', $redirectParams).'#post-'.$post->id)
            ->with('status', 'Comment added.');
    }

    public function vote(Request $request, CommunityPost $post): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'value' => ['required', 'integer', Rule::in([-1, 1])],
        ]);

        $existing = $post->votes()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && $existing->value === (int) $data['value']) {
            $existing->delete();
        } else {
            $post->votes()->updateOrCreate(
                ['user_id' => $request->user()->id],
                ['value' => (int) $data['value']],
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'post_id' => $post->id,
                'score' => (int) $post->votes()->sum('value'),
                'user_vote' => (int) ($post->votes()
                    ->where('user_id', $request->user()->id)
                    ->value('value') ?? 0),
            ]);
        }

        return back();
    }

    public function share(Request $request, CommunityPost $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:500'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));

        $sharedPost = $request->user()->communityPosts()->create([
            'title' => 'Shared: '.Str::limit($post->title, 149, '...'),
            'body' => $body !== '' ? $body : 'Shared '.$post->user->name."'s post.",
            'tag' => 'SHOWCASE',
            'shared_post_id' => $post->id,
        ]);

        return redirect()
            ->to(route('community.index', ['filter' => 'NEW', 'thread' => $sharedPost->id]).'#post-'.$sharedPost->id)
            ->with('status', 'Shared to the community.');
    }

    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $originalPostId = $post->shared_post_id;

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        if ($originalPostId) {
            return redirect()
                ->to(route('community.index', ['thread' => $originalPostId]).'#post-'.$originalPostId)
                ->with('status', 'Shared post deleted. Original post was kept.');
        }

        $redirectParams = array_filter([
            'filter' => $request->input('filter') ?: 'MINE',
            'tag' => $request->input('tag'),
            'search' => $request->input('search'),
        ], fn ($value) => filled($value));

        return redirect()
            ->route('community.index', $redirectParams)
            ->with('status', 'Post deleted.');
    }
}
