@extends('layouts.app')

@php
    $avatarInitial = strtoupper(substr($user->name, 0, 1));
    $avatarUrl = $user->avatarUrl();
    $currentStreak = (int) ($user->streak?->current_streak ?? 0);
    $longestStreak = (int) ($user->streak?->longest_streak ?? 0);
    $activityCount = $recentSubmissions->count() + $recentPosts->count();
@endphp

@section('content')
    <section class="github-profile-shell">
        <aside class="github-profile-sidebar">
            <div class="github-avatar" aria-hidden="true">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="">
                @else
                    {{ $avatarInitial }}
                @endif
            </div>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <a class="button button--secondary button--full" href="#profile-settings">Edit profile</a>

            <dl class="github-profile-stats">
                <div><dt>Points</dt><dd>{{ $user->points }}</dd></div>
                <div><dt>Solved</dt><dd>{{ $acceptedSubmissions }}</dd></div>
                <div><dt>Posts</dt><dd>{{ $communityPostCount }}</dd></div>
            </dl>

            <section class="github-achievements">
                <h2>Achievements</h2>
                <div class="achievement-badges" aria-label="Achievements">
                    <span title="Profile started">YO</span>
                    @if($acceptedSubmissions > 0)
                        <span title="First accepted solution">AC</span>
                    @endif
                    @if($currentStreak > 0)
                        <span title="Active streak">{{ $currentStreak }}D</span>
                    @endif
                </div>
            </section>
        </aside>

        <main class="github-profile-main">
            <nav class="github-profile-tabs" aria-label="Profile navigation">
                <a class="active" href="{{ route('profile.edit') }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Zm11-16v5h5"/></svg>
                    Overview
                </a>
                <a href="#profile-settings">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Zm7.4-2a1 1 0 0 0 0-3l-1.5-.3a6.7 6.7 0 0 0-.8-1.9l.9-1.2a1 1 0 0 0-2.1-2.1l-1.2.9a6.7 6.7 0 0 0-1.9-.8L12.5 4a1 1 0 0 0-3 0l-.3 1.5a6.7 6.7 0 0 0-1.9.8L6.1 5.4A1 1 0 0 0 4 7.5l.9 1.2a6.7 6.7 0 0 0-.8 1.9l-1.5.3a1 1 0 0 0 0 3l1.5.3a6.7 6.7 0 0 0 .8 1.9L4 17.3a1 1 0 0 0 2.1 2.1l1.2-.9a6.7 6.7 0 0 0 1.9.8l.3 1.5a1 1 0 0 0 3 0l.3-1.5a6.7 6.7 0 0 0 1.9-.8l1.2.9a1 1 0 0 0 2.1-2.1l-.9-1.2a6.7 6.7 0 0 0 .8-1.9l1.5-.3Z"/></svg>
                    Settings
                </a>
            </nav>

            <section class="github-profile-notice">
                <p>Your Coddy profile shows practice progress, community activity, and recent contributions.</p>
            </section>

            <section class="github-settings-card" id="profile-settings">
                <header>
                    <h2>Profile settings</h2>
                    <p>Update your display name and local practice timezone.</p>
                </header>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <label class="github-avatar-upload">
                        Profile picture
                        <span>
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="">
                            @else
                                <b aria-hidden="true">{{ $avatarInitial }}</b>
                            @endif
                            <input type="file" name="avatar" accept="image/*">
                        </span>
                        <small>Upload JPG, PNG, GIF, or WebP up to 2 MB.</small>
                    </label>
                    <label>Name <input name="name" value="{{ old('name', $user->name) }}" required></label>
                    <label>Timezone
                        <select name="timezone" required>
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $user->timezone) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit">Save changes</button>
                </form>
            </section>

            <section class="github-contributions">
                <header>
                    <h2>{{ $calendar['total_answers'] }} contributions in the last 12 weeks</h2>
                    <span>{{ $calendar['from'] }} to {{ $calendar['to'] }}</span>
                </header>
                <div class="github-heatmap" role="grid" aria-label="Coddy activity calendar from {{ $calendar['from'] }} to {{ $calendar['to'] }}">
                    <div class="github-month-row">
                        @foreach($calendar['months'] as $month)
                            <span>{{ $month }}</span>
                        @endforeach
                    </div>
                    <div class="github-graph">
                        <div class="github-weekday-labels" aria-hidden="true">
                            <span></span>
                            <span>Mon</span>
                            <span></span>
                            <span>Wed</span>
                            <span></span>
                            <span>Fri</span>
                            <span></span>
                        </div>
                        <div class="github-weeks">
                            @foreach($calendar['weeks'] as $week)
                                <div class="github-week" role="row">
                                    @foreach($week as $day)
                                        @if($day)
                                            <span
                                                role="gridcell"
                                                tabindex="{{ $day['answer_count'] > 0 ? 0 : -1 }}"
                                                class="github-day level-{{ $day['intensity'] }} {{ $day['is_today'] ? 'today' : '' }}"
                                                title="{{ $day['label'] }}: {{ $day['answer_count'] }} answers, {{ $day['accepted_answer_count'] }} accepted"
                                                aria-label="{{ $day['label'] }}: {{ $day['answer_count'] }} answers, {{ $day['accepted_answer_count'] }} accepted"
                                            ></span>
                                        @else
                                            <span class="github-day empty" aria-hidden="true"></span>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="github-heatmap-legend">Less <span class="github-day level-0"></span><span class="github-day level-1"></span><span class="github-day level-2"></span><span class="github-day level-3"></span><span class="github-day level-4"></span> More</p>
                </div>
            </section>

            <section class="github-activity">
                <h2>Contribution activity</h2>
                @if($activityCount > 0)
                    <div class="github-activity-list">
                        @foreach($recentSubmissions as $submission)
                            <article>
                                <span class="activity-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <div>
                                    <strong>{{ $submission->verdict === 'ACCEPTED' ? 'Solved' : 'Submitted' }} {{ $submission->exercise?->title ?? 'a problem' }}</strong>
                                    <p>{{ $submission->created_at->format('F j, Y') }}</p>
                                </div>
                            </article>
                        @endforeach
                        @foreach($recentPosts as $post)
                            <article>
                                <span class="activity-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M4 5h16v12H7l-3 3V5Z"/></svg>
                                </span>
                                <div>
                                    <strong>Created community post</strong>
                                    <p>{{ $post->title }} &middot; {{ $post->created_at->format('F j, Y') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="github-activity-empty">
                        <p>No contribution activity yet.</p>
                    </div>
                @endif
            </section>
        </main>
    </section>
@endsection
