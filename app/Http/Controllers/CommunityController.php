<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->with('user', 'comments.user')
            ->withCount('comments')
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

        return redirect()
            ->to(route('community.index').'#post-'.$post->id)
            ->with('status', 'Comment added.');
    }

    public function vote(Request $request, CommunityPost $post): RedirectResponse
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

        return back();
    }

    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()
            ->route('community.index', ['filter' => 'MINE'])
            ->with('status', 'Post deleted.');
    }
}
