@extends('layouts.app')

@php
    $avatarInitial = strtoupper(substr($user->name, 0, 1));
    $avatarUrl = $user->avatarUrl();
    $currentStreak = (int) ($user->streak?->current_streak ?? 0);
    $longestStreak = (int) ($user->streak?->longest_streak ?? 0);
    $activityCount = $recentSubmissions->count() + $recentPosts->count();
    $anonymousUsername = Str::studly(Str::before($user->email, '@')).$user->id;
    $solvedProblems = (int) $solvedDifficultyCounts->sum();
    $solvedPercent = $totalProblems > 0 ? min(100, (int) round(($solvedProblems / $totalProblems) * 100)) : 0;
    $difficultyLabels = [
        'EASY' => 'Easy',
        'MEDIUM' => 'Medium',
        'HARD' => 'Hard',
    ];
@endphp

@section('content')
    <section class="github-profile-shell profile-overview-shell">
        <aside class="profile-overview-sidebar">
            <section class="profile-identity-card">
                <div class="profile-identity-header">
                    <div class="profile-identity-avatar" aria-hidden="true">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="">
                        @else
                            {{ $avatarInitial }}
                        @endif
                    </div>
                    <div>
                        <h1>{{ $user->name }}</h1>
                        <p>
                            {{ '@'.$anonymousUsername }}
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M4 8h10v12H4z"/></svg>
                        </p>
                    </div>
                </div>

                <div class="profile-identity-meta">
                    <p>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                        {{ $user->email }}
                    </p>
                    <span>Private</span>
                </div>

                <a class="button button--secondary button--full" href="{{ route('profile.edit') }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L7 21H3v-4z"/></svg>
                    Edit Profile
                </a>
            </section>

            <div class="profile-streak-grid">
                <article>
                    <span>Current Streak</span>
                    <strong>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 22c4 0 7-3 7-7 0-2.5-1.2-4.7-3.2-6.5.2 1.9-.5 3.2-1.7 4.1.2-3.4-1.6-6.5-4.8-8.6.4 3.2-.7 5-2.2 6.7A7.4 7.4 0 0 0 5 15c0 4 3 7 7 7Z"/></svg>
                        {{ $currentStreak }} {{ Str::plural('day', $currentStreak) }}
                    </strong>
                </article>
                <article>
                    <span>Best Streak</span>
                    <strong>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M5 5H3v2a4 4 0 0 0 4 4M19 5h2v2a4 4 0 0 1-4 4"/></svg>
                        {{ $longestStreak }} {{ Str::plural('day', $longestStreak) }}
                    </strong>
                </article>
            </div>

            <section class="profile-rank-card">
                <header>
                    <strong>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M5 5H3v2a4 4 0 0 0 4 4M19 5h2v2a4 4 0 0 1-4 4"/></svg>
                        Top {{ number_format($rankSummary['top_percent'], 1) }}%
                    </strong>
                    <span>{{ $solvedProblems }} solved</span>
                </header>
                <div class="profile-rank-bars" aria-label="Solved problem distribution">
                    @foreach($rankSummary['distribution'] as $bucket)
                        <span
                            @class(['is-current' => $bucket['is_current']])
                            style="height: {{ $bucket['height'] }}%"
                            title="{{ $bucket['label'] }}: {{ $bucket['count'] }} learners"
                        ></span>
                    @endforeach
                </div>
                <p title="Rank is based on distinct accepted published problems among active learners.">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/></svg>
                    Rank #{{ $rankSummary['rank'] }} of {{ $rankSummary['total_users'] }} learners
                </p>
            </section>

            <section class="profile-solved-card">
                <h2>Coddy All</h2>
                <div class="profile-solved-summary">
                    <dl>
                        @foreach($difficultyLabels as $key => $label)
                            <div class="profile-difficulty profile-difficulty--{{ strtolower($key) }}">
                                <dt>{{ $label }}</dt>
                                <dd>{{ (int) ($solvedDifficultyCounts[$key] ?? 0) }} / {{ (int) ($difficultyCounts[$key] ?? 0) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="profile-solved-ring" style="--solved-angle: {{ $solvedPercent * 3.6 }}deg">
                        <strong>{{ $solvedProblems }}</strong>
                        <span>/{{ $totalProblems }}</span>
                        <small>Solved</small>
                    </div>
                </div>
            </section>
        </aside>

        <main class="profile-overview-main">
            <section class="github-contributions github-contributions--year">
                <header>
                    <h2>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg>
                        {{ $calendar['total_answers'] }} {{ Str::plural('submission', $calendar['total_answers']) }} in {{ $profileYear }}
                    </h2>
                    <div class="github-contribution-stats">
                        <span>Active days <strong>{{ $calendarActiveDays }}</strong></span>
                        <span>Current streak <strong>{{ $currentStreak }} {{ Str::plural('day', $currentStreak) }}</strong></span>
                        <span>Max streak <strong>{{ $longestStreak }} {{ Str::plural('day', $longestStreak) }}</strong></span>
                        <button type="button">
                            {{ $profileYear }}
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                        </button>
                    </div>
                </header>
                <div class="github-heatmap" role="grid" aria-label="Coddy activity calendar for {{ $profileYear }}">
                    <div class="github-month-row">
                        @foreach($calendar['months'] as $month)
                            <span @class(['is-month-start' => filled($month) && ! $loop->first])>{{ $month }}</span>
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
                                <div @class(['github-week', 'is-month-start' => filled($calendar['months'][$loop->index] ?? null) && ! $loop->first]) role="row">
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
                    <div class="github-heatmap-footer">
                        <p>Streaks reset at midnight UTC. Includes all submissions.</p>
                        <p class="github-heatmap-legend">Less <span class="github-day level-0"></span><span class="github-day level-1"></span><span class="github-day level-2"></span><span class="github-day level-3"></span><span class="github-day level-4"></span> More</p>
                    </div>
                </div>
            </section>

            <section class="profile-progress-panel">
                <header>
                    <h2>Your Progress</h2>
                    <p>Track your learning journey</p>
                </header>

                <section class="profile-problem-progress">
                    <h3>Problems</h3>
                    <div class="profile-problem-grid">
                        @forelse($profileProblemSets as $problemSet)
                            <article class="profile-problem-card">
                                <span>PROBLEM TOPIC</span>
                                <div>
                                    <a href="{{ route('catalog.index', ['search' => $problemSet->name]) }}">{{ $problemSet->name }}</a>
                                    <strong>{{ $problemSet->solved_exercises_count }} / {{ $problemSet->published_exercises_count }}</strong>
                                </div>
                                <p class="profile-problem-track" aria-label="{{ $problemSet->progress_percent }} percent complete">
                                    <b style="width: {{ $problemSet->progress_percent }}%"></b>
                                </p>
                                <a class="profile-problem-link" href="{{ route('catalog.index', ['search' => $problemSet->name]) }}">
                                    {{ $problemSet->published_exercises_count - $problemSet->solved_exercises_count > 0 ? 'Show '.($problemSet->published_exercises_count - $problemSet->solved_exercises_count).' more' : 'Review solved' }}
                                </a>
                            </article>
                        @empty
                            <div class="profile-empty-card">
                                <p>No published problems yet.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>

            <section class="github-activity profile-activity-card">
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
