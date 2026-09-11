@extends('layouts.app')

@php
    $feedQuery = fn (array $params = []) => route('community.index', array_filter(array_merge([
        'filter' => $filter,
        'tag' => $tag ?: null,
        'search' => $search ?: null,
    ], $params), fn ($value) => filled($value)));
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

            <section class="community-side-card community-search-card">
                <h2>Search</h2>
                <form class="community-search" method="GET" action="{{ route('community.index') }}">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    @if($tag)
                        <input type="hidden" name="tag" value="{{ $tag }}">
                    @endif
                    <label>
                        <span class="sr-only">Search community</span>
                        <input name="search" value="{{ $search }}" placeholder="Search posts">
                    </label>
                    <button type="submit" class="button--secondary">Search</button>
                </form>
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
                    @endphp
                    <article class="community-post" id="post-{{ $post->id }}">
                        <div class="post-votes" aria-label="Post score">
                            <form method="POST" action="{{ route('community.vote', $post) }}">
                                @csrf
                                <input type="hidden" name="value" value="1">
                                <button class="{{ $userVote === 1 ? 'active' : '' }}" type="submit" aria-label="Upvote {{ $post->title }}">^</button>
                            </form>
                            <strong>{{ $score }}</strong>
                            <form method="POST" action="{{ route('community.vote', $post) }}">
                                @csrf
                                <input type="hidden" name="value" value="-1">
                                <button class="{{ $userVote === -1 ? 'active' : '' }}" type="submit" aria-label="Downvote {{ $post->title }}">v</button>
                            </form>
                        </div>
                        <div class="post-body">
                            <header class="post-meta">
                                <span class="post-tag">{{ str_replace('_', ' ', ucfirst(strtolower($post->tag))) }}</span>
                                <span>Posted by {{ $post->user->name }}</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                @if($canManagePost)
                                    <details class="post-menu">
                                        <summary aria-label="Open post actions">...</summary>
                                        <div>
                                            <form method="POST" action="{{ route('community.destroy', $post) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete post</button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                            </header>
                            <h2>{{ $post->title }}</h2>
                            <p>{{ $post->body }}</p>
                            @if($post->imageUrl())
                                <a class="post-image" href="{{ $post->imageUrl() }}" target="_blank" rel="noreferrer">
                                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}">
                                </a>
                            @endif
                            <footer class="post-footer">
                                <span>{{ $post->comments_count }} comments</span>
                                <span>{{ $score >= 0 ? '+' : '' }}{{ $score }} score</span>
                            </footer>

                            <section class="post-comments" aria-label="Comments for {{ $post->title }}">
                                @foreach($post->comments->take(3) as $comment)
                                    <article class="post-comment">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <p>{{ $comment->body }}</p>
                                    </article>
                                @endforeach
                                <form method="POST" action="{{ route('community.comments.store', $post) }}" class="comment-form">
                                    @csrf
                                    <label>
                                        <span class="sr-only">Comment</span>
                                        <input name="body" maxlength="2000" placeholder="Add a comment" required>
                                    </label>
                                    <button type="submit">Reply</button>
                                </form>
                            </section>
                        </div>
                    </article>
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
@endsection
