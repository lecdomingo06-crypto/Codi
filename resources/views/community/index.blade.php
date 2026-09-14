@extends('layouts.app')

@php
    $feedQuery = fn (array $params = []) => route('community.index', array_filter(array_merge([
        'filter' => $filter,
        'tag' => $tag ?: null,
        'search' => $search ?: null,
    ], $params), fn ($value) => filled($value)));

    $compactNumber = function (int $value): string {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K';
        }

        return (string) $value;
    };
@endphp

@section('content')
    <section class="community-shell">
        <aside class="community-sidebar" aria-label="Community sidebar">
            <section class="community-nav-card">
                <header>
                    <strong>Community</strong>
                </header>

                <button class="community-create-button" type="button" data-community-compose-open>
                    <span aria-hidden="true">+</span>
                    <strong>Create Post</strong>
                </button>

                <nav class="community-nav" aria-label="Community navigation">
                    <a class="{{ $filter === 'HOT' && ! $tag ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'HOT', 'tag' => null]) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3c1.7 2.6 5 5.2 5 9a5 5 0 0 1-10 0c0-1.8 1-3.3 2.2-4.4-.1 1.6.6 2.7 1.8 3.4.2-3.4.7-5.7 1-8Z"/></svg>
                        <span>Hot</span>
                    </a>
                    <a class="{{ $filter === 'NEW' && ! $tag ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'NEW', 'tag' => null]) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 5v14m7-7H5"/></svg>
                        <span>New</span>
                    </a>
                    <a class="{{ $filter === 'MINE' ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'MINE', 'tag' => null]) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Zm11-16v5h5M8 13h8M8 17h5"/></svg>
                        <span>My Posts</span>
                    </a>
                    <a class="{{ $tag === 'QUESTION' ? 'active' : '' }}" href="{{ $feedQuery(['tag' => 'QUESTION']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9.1 9a3 3 0 1 1 5.8 1c-.7 1.4-2.9 1.5-2.9 3.5M12 18h.01"/></svg>
                        <span>Questions</span>
                    </a>
                    <a class="{{ $tag === 'HELP' ? 'active' : '' }}" href="{{ $feedQuery(['tag' => 'HELP']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21a9 9 0 1 0-9-9m9 4v.01M9.5 9a2.8 2.8 0 1 1 4.9 1.8c-.9.9-2.4 1.1-2.4 2.7"/></svg>
                        <span>Help</span>
                    </a>
                    <a class="{{ $tag === 'BUG' ? 'active' : '' }}" href="{{ $feedQuery(['tag' => 'BUG']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 7V5m8 2V5M6 13H3m18 0h-3M7 19l2-2m8 2-2-2M7 8l2 2m8-2-2 2M8 11h8v4a4 4 0 0 1-8 0v-4Z"/></svg>
                        <span>Bugs</span>
                    </a>
                    <a class="{{ $tag === 'SHOWCASE' ? 'active' : '' }}" href="{{ $feedQuery(['tag' => 'SHOWCASE']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 19h16M6 17l4-10 3 6 2-4 3 8"/></svg>
                        <span>Showcase</span>
                    </a>
                    <a class="{{ $tag === 'STUDY_BUDDY' ? 'active' : '' }}" href="{{ $feedQuery(['tag' => 'STUDY_BUDDY']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 0 4 4M8 11a4 4 0 1 0-4 4m2 5a6 6 0 0 1 12 0"/></svg>
                        <span>Study Buddy</span>
                    </a>
                </nav>
            </section>

            <section class="community-side-card">
                <h2>Popular Tags</h2>
                <div class="community-tags">
                    @foreach(\App\Models\CommunityPost::TAGS as $option)
                        <a href="{{ route('community.index', ['tag' => $option]) }}">
                            {{ str_replace('_', ' ', ucfirst(strtolower($option))) }}
                            <span>{{ (int) ($tagCounts[$option] ?? 0) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="community-side-card community-policy-card">
                <h2>Cody</h2>
                <nav aria-label="Community policy links">
                    <a href="#">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6l-7-3Z"/></svg>
                        <span>Cody Rules</span>
                    </a>
                    <a href="#">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 11V7a4 4 0 0 1 8 0v4m-9 0h10v9H7z"/></svg>
                        <span>Privacy Policy</span>
                    </a>
                    <a href="#">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 4h9l3 3v13H6zM14 4v4h4M9 13h6M9 17h4"/></svg>
                        <span>User Agreement</span>
                    </a>
                    <a href="#">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 5a2 2 0 1 0 0 .01M5 10h14m-7 0v10m-4-5 4-5 4 5"/></svg>
                        <span>Accessibility</span>
                    </a>
                </nav>
            </section>
        </aside>

        <main class="community-feed" aria-label="Community feed">
            <section class="community-sortbar" aria-label="Feed filters">
                <a class="{{ $filter === 'HOT' ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'HOT']) }}">Hot</a>
                <a class="{{ $filter === 'NEW' ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'NEW']) }}">New</a>
                <a class="{{ $filter === 'MINE' ? 'active' : '' }}" href="{{ $feedQuery(['filter' => 'MINE', 'tag' => null]) }}">My Posts</a>
                @foreach(\App\Models\CommunityPost::TAGS as $option)
                    <a class="{{ $tag === $option ? 'active' : '' }}" href="{{ $feedQuery(['tag' => $option]) }}">{{ str_replace('_', ' ', ucfirst(strtolower($option))) }}</a>
                @endforeach
            </section>

            <section class="community-posts">
                @forelse($posts as $post)
                    @php
                        $score = (int) ($post->vote_score ?? 0);
                        $userVote = (int) ($userVotes[$post->id] ?? 0);
                        $canManagePost = $post->user_id === auth()->id() || auth()->user()->isAdmin();
                        $commentCount = (int) $post->comments_count;
                        $shareCount = (int) ($post->shares_count ?? 0);
                        $postImageUrl = $post->imageUrl();
                        $postThreadUrl = route('community.index', ['thread' => $post->id]).'#post-'.$post->id;
                        $sharedPost = $post->sharedPost;
                        $sharedPostImageUrl = $sharedPost?->imageUrl();
                        $threadComments = $post->comments->sortByDesc('created_at');
                        $deleteLabel = $post->shared_post_id ? 'Delete share' : 'Delete post';
                    @endphp
                    <article class="community-post" id="post-{{ $post->id }}" data-community-post="{{ $post->id }}">
                        <div class="post-votes" aria-label="Post score">
                            <form method="POST" action="{{ route('community.vote', $post) }}" data-community-vote-form data-post-id="{{ $post->id }}">
                                @csrf
                                <input type="hidden" name="value" value="1">
                                <button class="{{ $userVote === 1 ? 'active' : '' }}" type="submit" aria-label="Upvote {{ $post->title }}" data-community-vote-button data-vote-value="1">^</button>
                            </form>
                            <strong data-community-vote-score="{{ $post->id }}" data-score-style="plain">{{ $score }}</strong>
                            <form method="POST" action="{{ route('community.vote', $post) }}" data-community-vote-form data-post-id="{{ $post->id }}">
                                @csrf
                                <input type="hidden" name="value" value="-1">
                                <button class="{{ $userVote === -1 ? 'active' : '' }}" type="submit" aria-label="Downvote {{ $post->title }}" data-community-vote-button data-vote-value="-1">v</button>
                            </form>
                        </div>
                        <div class="post-body">
                            <header class="post-meta">
                                <a class="post-author-link" href="{{ route('community.users.show', $post->user) }}">
                                    <span class="post-author-avatar" aria-hidden="true">
                                        @if($post->user->avatarUrl())
                                            <img src="{{ $post->user->avatarUrl() }}" alt="">
                                        @else
                                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                        @endif
                                    </span>
                                    <span>Posted by {{ $post->user->name }}</span>
                                </a>
                                <span class="post-tag">{{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                @if($canManagePost)
                                    <details class="post-menu">
                                        <summary aria-label="Open post actions">...</summary>
                                        <div>
                                            <form method="POST" action="{{ route('community.destroy', $post) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="filter" value="{{ $filter }}">
                                                @if($tag)
                                                    <input type="hidden" name="tag" value="{{ $tag }}">
                                                @endif
                                                @if($search)
                                                    <input type="hidden" name="search" value="{{ $search }}">
                                                @endif
                                                <button type="submit">{{ $deleteLabel }}</button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                            </header>
                            <h2>{{ $post->title }}</h2>
                            <p>{{ $post->body }}</p>
                            @if($postImageUrl)
                                <button
                                    class="post-image post-image-button"
                                    type="button"
                                    data-community-thread-open="{{ $post->id }}"
                                    aria-controls="community-thread-{{ $post->id }}"
                                    aria-label="Open {{ $post->title }} post view"
                                >
                                    <img src="{{ $postImageUrl }}" alt="{{ $post->title }}">
                                </button>
                            @endif
                            @if($sharedPost)
                                <a class="shared-post-preview" href="{{ route('community.index', ['thread' => $sharedPost->id]).'#post-'.$sharedPost->id }}">
                                    @if($sharedPostImageUrl)
                                        <img src="{{ $sharedPostImageUrl }}" alt="">
                                    @endif
                                    <span>Shared from {{ $sharedPost->user->name }}</span>
                                    <strong>{{ $sharedPost->title }}</strong>
                                    <p>{{ \Illuminate\Support\Str::limit($sharedPost->body, 150) }}</p>
                                </a>
                            @endif
                            <footer class="post-footer post-action-strip" aria-label="Post engagement">
                                <button
                                    class="post-action-button"
                                    type="button"
                                    data-community-thread-open="{{ $post->id }}"
                                    aria-controls="community-thread-{{ $post->id }}"
                                    aria-label="View {{ $commentCount }} {{ $commentCount === 1 ? 'comment' : 'comments' }}"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-8 8H7l-4 2 1.5-4A8 8 0 1 1 21 12Z"/></svg>
                                    <span>{{ $compactNumber($commentCount) }}</span>
                                </button>
                                <button
                                    class="post-action-button"
                                    type="button"
                                    data-community-share-open="{{ $post->id }}"
                                    aria-controls="community-share-{{ $post->id }}"
                                    aria-label="Share {{ $post->title }}"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12v7h16v-7M12 15V4m0 0 4 4m-4-4-4 4"/></svg>
                                    <span>{{ $shareCount > 0 ? $compactNumber($shareCount) : 'Share' }}</span>
                                </button>
                            </footer>
                        </div>
                    </article>

                    <div
                        class="community-thread-modal"
                        id="community-thread-{{ $post->id }}"
                        data-community-thread-modal
                        data-thread-id="{{ $post->id }}"
                        hidden
                    >
                        <div class="community-thread-backdrop" data-community-thread-close></div>
                        <section class="community-thread-card" role="dialog" aria-modal="true" aria-labelledby="community-thread-title-{{ $post->id }}">
                            <header class="community-thread-header">
                                <h2 id="community-thread-title-{{ $post->id }}">{{ $post->user->name }}'s Post</h2>
                                <button class="community-thread-close" type="button" data-community-thread-close aria-label="Close comments">x</button>
                            </header>

                            <div class="community-thread-scroll">
                                <article class="thread-post">
                                    <header class="thread-author">
                                        <a class="thread-avatar" href="{{ route('community.users.show', $post->user) }}" aria-label="View {{ $post->user->name }} profile">
                                            @if($post->user->avatarUrl())
                                                <img src="{{ $post->user->avatarUrl() }}" alt="">
                                            @else
                                                {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                            @endif
                                        </a>
                                        <div>
                                            <a class="thread-author-name" href="{{ route('community.users.show', $post->user) }}"><strong>{{ $post->user->name }}</strong></a>
                                            <span>{{ $post->created_at->diffForHumans() }} &middot; {{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                                        </div>

                                        @if($canManagePost)
                                            <details class="post-menu thread-menu">
                                                <summary aria-label="Open post actions">...</summary>
                                                <div>
                                                    <form method="POST" action="{{ route('community.destroy', $post) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="filter" value="{{ $filter }}">
                                                        @if($tag)
                                                            <input type="hidden" name="tag" value="{{ $tag }}">
                                                        @endif
                                                        @if($search)
                                                            <input type="hidden" name="search" value="{{ $search }}">
                                                        @endif
                                                        <button type="submit">{{ $deleteLabel }}</button>
                                                    </form>
                                                </div>
                                            </details>
                                        @endif
                                    </header>

                                    <h3>{{ $post->title }}</h3>
                                    <p>{{ $post->body }}</p>

                                    @if($postImageUrl)
                                        <a class="thread-image" href="{{ $postImageUrl }}" target="_blank" rel="noreferrer">
                                            <img src="{{ $postImageUrl }}" alt="{{ $post->title }}">
                                        </a>
                                    @endif
                                    @if($sharedPost)
                                        <a class="shared-post-preview shared-post-preview--thread" href="{{ route('community.index', ['thread' => $sharedPost->id]).'#post-'.$sharedPost->id }}">
                                            @if($sharedPostImageUrl)
                                                <img src="{{ $sharedPostImageUrl }}" alt="">
                                            @endif
                                            <span>Shared from {{ $sharedPost->user->name }}</span>
                                            <strong>{{ $sharedPost->title }}</strong>
                                            <p>{{ \Illuminate\Support\Str::limit($sharedPost->body, 150) }}</p>
                                        </a>
                                    @endif
                                </article>

                                <section class="thread-engagement" aria-label="Post engagement">
                                    <div class="thread-reactions">
                                        <span aria-hidden="true">^</span>
                                        <strong data-community-vote-score="{{ $post->id }}" data-score-style="compact">{{ $score < 0 ? '-' : '' }}{{ $compactNumber(abs($score)) }}</strong>
                                    </div>
                                    <button type="button" data-community-thread-comment-focus>
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-8 8H7l-4 2 1.5-4A8 8 0 1 1 21 12Z"/></svg>
                                        <span>{{ $compactNumber($commentCount) }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-community-share-open="{{ $post->id }}"
                                        aria-controls="community-share-{{ $post->id }}"
                                    >
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12v7h16v-7M12 15V4m0 0 4 4m-4-4-4 4"/></svg>
                                        <span>{{ $shareCount > 0 ? $compactNumber($shareCount) : 'Share' }}</span>
                                    </button>
                                </section>

                                <section class="thread-comments" aria-label="Comments">
                                    <header>
                                        <button type="button">Most relevant</button>
                                    </header>

                                    @forelse($threadComments as $comment)
                                        <article class="thread-comment">
                                            <a class="thread-avatar thread-avatar--small" href="{{ route('community.users.show', $comment->user) }}" aria-label="View {{ $comment->user->name }} profile">
                                                @if($comment->user->avatarUrl())
                                                    <img src="{{ $comment->user->avatarUrl() }}" alt="">
                                                @else
                                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                @endif
                                            </a>
                                            <div>
                                                <div class="thread-comment-bubble">
                                                    <a class="thread-comment-author" href="{{ route('community.users.show', $comment->user) }}">{{ $comment->user->name }}</a>
                                                    <p>{{ $comment->body }}</p>
                                                </div>
                                                <footer>
                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                    <button type="button" data-community-thread-comment-focus>Reply</button>
                                                    <button
                                                        type="button"
                                                        data-community-share-open="{{ $post->id }}"
                                                        aria-controls="community-share-{{ $post->id }}"
                                                    >Share</button>
                                                </footer>
                                            </div>
                                        </article>
                                    @empty
                                        <p class="thread-empty">No comments yet. Start the conversation.</p>
                                    @endforelse
                                </section>
                            </div>

                            <form method="POST" action="{{ route('community.comments.store', $post) }}" class="thread-reply-bar">
                                @csrf
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                @if($tag)
                                    <input type="hidden" name="tag" value="{{ $tag }}">
                                @endif
                                @if($search)
                                    <input type="hidden" name="search" value="{{ $search }}">
                                @endif
                                <span class="thread-avatar thread-avatar--small" aria-hidden="true">
                                    @if(auth()->user()->avatarUrl())
                                        <img src="{{ auth()->user()->avatarUrl() }}" alt="">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </span>
                                <label>
                                    <span class="sr-only">Comment</span>
                                    <input name="body" maxlength="2000" placeholder="Comment as {{ auth()->user()->name }}" data-community-thread-input required>
                                </label>
                                <button type="submit" aria-label="Post comment">
                                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/></svg>
                                </button>
                            </form>
                        </section>
                    </div>

                    <div
                        class="community-share-modal"
                        id="community-share-{{ $post->id }}"
                        data-community-share-modal
                        data-share-id="{{ $post->id }}"
                        hidden
                    >
                        <div class="community-share-backdrop" data-community-share-close></div>
                        <section class="community-share-card" role="dialog" aria-modal="true" aria-labelledby="community-share-title-{{ $post->id }}">
                            <header class="community-share-header">
                                <h2 id="community-share-title-{{ $post->id }}">Share</h2>
                                <button class="community-thread-close" type="button" data-community-share-close aria-label="Close share dialog">x</button>
                            </header>

                            <form method="POST" action="{{ route('community.share', $post) }}" class="share-composer">
                                @csrf
                                <div class="share-author">
                                    <span class="thread-avatar thread-avatar--small" aria-hidden="true">
                                        @if(auth()->user()->avatarUrl())
                                            <img src="{{ auth()->user()->avatarUrl() }}" alt="">
                                        @else
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        @endif
                                    </span>
                                    <div>
                                        <strong>{{ auth()->user()->name }}</strong>
                                        <span>Community feed</span>
                                    </div>
                                </div>

                                <label class="share-message">
                                    <span class="sr-only">Share message</span>
                                    <textarea name="body" rows="3" maxlength="500" placeholder="Say something about this..." data-community-share-message></textarea>
                                </label>

                                <article class="share-preview">
                                    <span>{{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                                    <strong>{{ $post->title }}</strong>
                                    <p>{{ \Illuminate\Support\Str::limit($post->body, 120) }}</p>
                                </article>

                                <button
                                    class="share-now-button"
                                    type="submit"
                                >
                                    Share to Community
                                </button>

                                <section class="share-options" aria-label="Share destinations">
                                    <h3>Share to</h3>
                                    <div>
                                        <button type="button" data-community-share-copy data-share-url="{{ $postThreadUrl }}">
                                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.1-1.1"/></svg>
                                            <span>Copy link</span>
                                        </button>
                                        <a href="mailto:?subject={{ rawurlencode($post->title) }}&body={{ rawurlencode($postThreadUrl) }}">
                                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16v12H4zM4 7l8 6 8-6"/></svg>
                                            <span>Email</span>
                                        </a>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($postThreadUrl) }}" target="_blank" rel="noreferrer">
                                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9a1 1 0 0 1 1-1Z"/></svg>
                                            <span>Facebook</span>
                                        </a>
                                    </div>
                                </section>

                                <p class="share-status" data-community-share-status aria-live="polite"></p>
                            </form>
                        </section>
                    </div>
                @empty
                    <section class="community-empty">
                        <h2>No community posts yet.</h2>
                        <p>Use the plus button in the sidebar to start the first thread.</p>
                    </section>
                @endforelse
            </section>
        </main>
    </section>

    <div class="community-compose-modal" data-community-compose-modal hidden>
        <div class="community-compose-backdrop" data-community-compose-close></div>
        <section class="community-compose-card" role="dialog" aria-modal="true" aria-labelledby="community-compose-title">
            <header>
                <div>
                    <h2 id="community-compose-title">Create Post</h2>
                    <p>Share a question, screenshot, bug, or project update.</p>
                </div>
                <button type="button" data-community-compose-close aria-label="Close create post form">x</button>
            </header>
            <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="composer-topline">
                    <label>
                        <span class="sr-only">Post tag</span>
                        <select name="tag" required>
                            @foreach(\App\Models\CommunityPost::TAGS as $option)
                                <option value="{{ $option }}" @selected(old('tag', 'QUESTION') === $option)>{{ str_replace('_', ' ', ucfirst(strtolower($option))) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="image-upload">
                        <input type="file" name="image" accept="image/*" data-community-image-input>
                        <span data-community-image-label>Add image</span>
                    </label>
                </div>
                <img class="composer-image-preview" data-community-image-preview alt="Selected post image preview" hidden>
                <label>
                    <span class="sr-only">Post title</span>
                    <input name="title" value="{{ old('title') }}" maxlength="160" placeholder="Title your question, showcase, or idea" required>
                </label>
                <label>
                    <span class="sr-only">Description</span>
                    <textarea name="body" rows="5" maxlength="5000" placeholder="Write the details. You can explain an error, ask for help, or share what you built." required>{{ old('body') }}</textarea>
                </label>
                <div class="composer-actions">
                    <small>Images up to 2 MB</small>
                    <button type="submit">Post</button>
                </div>
            </form>
        </section>
    </div>

    @if($errors->has('title') || $errors->has('body') || $errors->has('tag') || $errors->has('image'))
        <script>
            window.CODDY_OPEN_COMMUNITY_COMPOSER = true;
        </script>
    @endif

    @if(request('thread'))
        <script>
            window.CODDY_OPEN_COMMUNITY_THREAD = @json((string) request('thread'));
        </script>
    @endif
@endsection
