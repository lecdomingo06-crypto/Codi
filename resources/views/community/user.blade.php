@extends('layouts.app')

@php
    $profileAvatarUrl = $profileUser->avatarUrl();
    $profileInitial = strtoupper(substr($profileUser->name, 0, 1));
    $memberSince = $profileUser->created_at?->format('F Y') ?? 'Recently';
    $viewerAvatarUrl = auth()->user()->avatarUrl();
    $viewerInitial = strtoupper(substr(auth()->user()->name, 0, 1));

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
    <section class="community-profile-shell">
        <section class="community-profile-hero" aria-labelledby="community-profile-name">
            <div class="community-profile-cover" aria-hidden="true"></div>

            <div class="community-profile-main">
                <span class="community-profile-avatar" aria-hidden="true">
                    @if($profileAvatarUrl)
                        <img src="{{ $profileAvatarUrl }}" alt="">
                    @else
                        {{ $profileInitial }}
                    @endif
                </span>

                <div class="community-profile-title">
                    <h1 id="community-profile-name">{{ $profileUser->name }}</h1>
                    <p>{{ $compactNumber($postCount) }} {{ $postCount === 1 ? 'post' : 'posts' }} &middot; {{ $compactNumber($commentCount) }} {{ $commentCount === 1 ? 'comment' : 'comments' }} &middot; Joined {{ $memberSince }}</p>
                </div>

                <div class="community-profile-actions">
                    @if(auth()->id() === $profileUser->id)
                        <a class="button button--small" href="{{ route('profile.edit') }}">Edit Profile</a>
                    @endif
                    <a class="button button--secondary button--small" href="{{ route('community.index') }}">Community</a>
                </div>
            </div>

            <nav class="community-profile-tabs" aria-label="Community profile tabs">
                <a class="active" href="{{ route('community.users.show', $profileUser) }}">Posts</a>
                <a href="{{ route('community.index') }}">Community</a>
            </nav>
        </section>

        <section class="community-profile-grid">
            <aside class="community-profile-about" aria-label="{{ $profileUser->name }} details">
                <section class="community-profile-panel">
                    <h2>Personal details</h2>
                    <dl>
                        <div>
                            <dt>Role</dt>
                            <dd>{{ $profileUser->isAdmin() ? 'Admin' : 'Learner' }}</dd>
                        </div>
                        <div>
                            <dt>Community posts</dt>
                            <dd>{{ $compactNumber($postCount) }}</dd>
                        </div>
                        <div>
                            <dt>Comments</dt>
                            <dd>{{ $compactNumber($commentCount) }}</dd>
                        </div>
                        <div>
                            <dt>Shares</dt>
                            <dd>{{ $compactNumber($shareCount) }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="community-profile-panel">
                    <h2>Community stats</h2>
                    <div class="community-profile-stat-row">
                        <span>Posts</span>
                        <strong>{{ $compactNumber($postCount) }}</strong>
                    </div>
                    <div class="community-profile-stat-row">
                        <span>Comments</span>
                        <strong>{{ $compactNumber($commentCount) }}</strong>
                    </div>
                </section>
            </aside>

            <main class="community-profile-posts" aria-label="{{ $profileUser->name }} posts">
                <header class="community-profile-section-header">
                    <h2>Posts</h2>
                    <span>{{ $compactNumber($postCount) }}</span>
                </header>

                @forelse($posts as $post)
                    @php
                        $score = (int) ($post->vote_score ?? 0);
                        $commentCountForPost = (int) $post->comments_count;
                        $shareCountForPost = (int) ($post->shares_count ?? 0);
                        $postImageUrl = $post->imageUrl();
                        $sharedPost = $post->sharedPost;
                        $sharedPostImageUrl = $sharedPost?->imageUrl();
                        $sharedPostUrl = $sharedPost ? route('community.index', ['thread' => $sharedPost->id]).'#post-'.$sharedPost->id : null;
                        $postThreadUrl = route('community.users.show', ['user' => $profileUser->id, 'thread' => $post->id]).'#post-'.$post->id;
                        $threadComments = $post->comments->sortByDesc('created_at');
                    @endphp

                    <article class="community-profile-post" id="post-{{ $post->id }}">
                        <header>
                            <span class="post-author-avatar post-author-avatar--large" aria-hidden="true">
                                @if($profileAvatarUrl)
                                    <img src="{{ $profileAvatarUrl }}" alt="">
                                @else
                                    {{ $profileInitial }}
                                @endif
                            </span>
                            <div>
                                <strong>{{ $profileUser->name }}</strong>
                                <span>{{ $post->created_at->diffForHumans() }} &middot; {{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                            </div>
                        </header>

                        <h3>
                            <button type="button" data-community-thread-open="{{ $post->id }}" aria-controls="community-thread-{{ $post->id }}">
                                {{ $post->title }}
                            </button>
                        </h3>
                        <p>{{ $post->body }}</p>

                        @if($postImageUrl)
                            <button
                                class="community-profile-post-image community-profile-post-image-button"
                                type="button"
                                data-community-thread-open="{{ $post->id }}"
                                aria-controls="community-thread-{{ $post->id }}"
                                aria-label="Open {{ $post->title }} post view"
                            >
                                <img src="{{ $postImageUrl }}" alt="{{ $post->title }}">
                            </button>
                        @endif

                        @if($sharedPost)
                            <a class="shared-post-preview" href="{{ $sharedPostUrl }}">
                                @if($sharedPostImageUrl)
                                    <img src="{{ $sharedPostImageUrl }}" alt="">
                                @endif
                                <span>Shared from {{ $sharedPost->user->name }}</span>
                                <strong>{{ $sharedPost->title }}</strong>
                                <p>{{ \Illuminate\Support\Str::limit($sharedPost->body, 150) }}</p>
                            </a>
                        @endif

                        <footer class="community-profile-post-footer">
                            <span>
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m12 5 5 5h-3v8h-4v-8H7l5-5Z"/></svg>
                                {{ $score < 0 ? '-' : '' }}{{ $compactNumber(abs($score)) }}
                            </span>
                            <span>
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-8 8H7l-4 2 1.5-4A8 8 0 1 1 21 12Z"/></svg>
                                {{ $compactNumber($commentCountForPost) }}
                            </span>
                            <span>
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12v7h16v-7M12 15V4m0 0 4 4m-4-4-4 4"/></svg>
                                {{ $compactNumber($shareCountForPost) }}
                            </span>
                            <button type="button" data-community-thread-open="{{ $post->id }}" aria-controls="community-thread-{{ $post->id }}">
                                View discussion
                            </button>
                        </footer>
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
                                <h2 id="community-thread-title-{{ $post->id }}">{{ $profileUser->name }}'s Post</h2>
                                <button class="community-thread-close" type="button" data-community-thread-close aria-label="Close comments">x</button>
                            </header>

                            <div class="community-thread-scroll">
                                <article class="thread-post">
                                    <header class="thread-author">
                                        <span class="thread-avatar" aria-hidden="true">
                                            @if($profileAvatarUrl)
                                                <img src="{{ $profileAvatarUrl }}" alt="">
                                            @else
                                                {{ $profileInitial }}
                                            @endif
                                        </span>
                                        <div>
                                            <strong>{{ $profileUser->name }}</strong>
                                            <span>{{ $post->created_at->diffForHumans() }} &middot; {{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                                        </div>
                                    </header>

                                    <h3>{{ $post->title }}</h3>
                                    <p>{{ $post->body }}</p>

                                    @if($postImageUrl)
                                        <a class="thread-image" href="{{ $postImageUrl }}" target="_blank" rel="noreferrer">
                                            <img src="{{ $postImageUrl }}" alt="{{ $post->title }}">
                                        </a>
                                    @endif
                                    @if($sharedPost)
                                        <a class="shared-post-preview shared-post-preview--thread" href="{{ $sharedPostUrl }}">
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
                                        <span>{{ $compactNumber($commentCountForPost) }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-community-share-open="{{ $post->id }}"
                                        aria-controls="community-share-{{ $post->id }}"
                                    >
                                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 12v7h16v-7M12 15V4m0 0 4 4m-4-4-4 4"/></svg>
                                        <span>{{ $shareCountForPost > 0 ? $compactNumber($shareCountForPost) : 'Share' }}</span>
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
                                <input type="hidden" name="return_to" value="community-user">
                                <input type="hidden" name="profile_user_id" value="{{ $profileUser->id }}">
                                <span class="thread-avatar thread-avatar--small" aria-hidden="true">
                                    @if($viewerAvatarUrl)
                                        <img src="{{ $viewerAvatarUrl }}" alt="">
                                    @else
                                        {{ $viewerInitial }}
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
                                        @if($viewerAvatarUrl)
                                            <img src="{{ $viewerAvatarUrl }}" alt="">
                                        @else
                                            {{ $viewerInitial }}
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

                                <button class="share-now-button" type="submit">
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
                        <h2>No posts yet</h2>
                        <p>{{ $profileUser->name }} has not posted in the community yet.</p>
                    </section>
                @endforelse
            </main>
        </section>
    </section>

    @if(request('thread'))
        <script>
            window.CODDY_OPEN_COMMUNITY_THREAD = @json((string) request('thread'));
        </script>
    @endif
@endsection
