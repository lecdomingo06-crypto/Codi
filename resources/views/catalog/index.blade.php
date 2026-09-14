@extends('layouts.app')

@php
    $featuredCourses = $courses->take(3);
    $primaryCourse = $featuredCourses->first();
    $difficultyTotals = collect(\App\Models\Exercise::DIFFICULTIES)->mapWithKeys(fn ($level) => [$level => (int) ($difficultyCounts[$level] ?? 0)]);
    $difficultySolved = collect(\App\Models\Exercise::DIFFICULTIES)->mapWithKeys(fn ($level) => [$level => (int) ($solvedDifficultyCounts[$level] ?? 0)]);
    $solvedPercent = $totalProblems > 0 ? round(($solvedCount / $totalProblems) * 100) : 0;
    $calendarQuery = request()->query();
    $trackTitle = $primaryCourse?->title ?? 'Core Skills';
    $trackSummary = $primaryCourse?->description ?? 'Practice common data structures, algorithms, and coding interview problems.';
    $topTopic = $topics->first();
    $activeMenu = request('menu', 'problems');
    $userTimezone = auth()->user()->timezone ?: config('app.timezone');
    $todayLocal = now($userTimezone);
    $todayCalendar = collect($monthCalendar['days'])->firstWhere('date', $todayLocal->toDateString());
    $acceptedToday = (int) ($todayCalendar['accepted_answer_count'] ?? 0);
    $dailyGoal = 5;
    $dailyGoalProgress = min(100, (int) round(($acceptedToday / $dailyGoal) * 100));
    $resetAt = $todayLocal->copy()->endOfDay()->toIso8601String();
    $menuLink = fn (string $menu, array $params = []) => route('catalog.index', array_filter(array_merge([
        'menu' => $menu === 'problems' ? null : $menu,
        'month' => request('month'),
    ], $params), fn ($value) => filled($value)));
@endphp

@section('content')
    <section class="practice-shell" data-problem-browser>
        <aside class="practice-menu-card" aria-label="Practice menu">
            <header>
                <strong>Menu</strong>
                <span aria-hidden="true">&lt;-</span>
            </header>
            <nav class="practice-menu" aria-label="Problem categories">
                <section class="practice-menu-section">
                    <a class="practice-menu__group {{ in_array($activeMenu, ['problems', 'company', 'cheatsheets', 'quizzes'], true) ? 'is-open' : '' }}" href="{{ $menuLink('problems') }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 8-4 4 4 4m8-8 4 4-4 4m-2-11-4 14"/></svg>
                        <span>Coding Interviews</span>
                        <b aria-hidden="true">v</b>
                    </a>
                    <div class="practice-submenu">
                        <a class="{{ $activeMenu === 'problems' ? 'is-active' : '' }}" href="{{ $menuLink('problems') }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            <span>Problems</span>
                        </a>
                        <a class="{{ $activeMenu === 'company' ? 'is-active' : '' }}" href="{{ $menuLink('company', ['sort' => 'difficulty']) }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 21V7l8-4 8 4v14M9 21v-8h6v8M8 9h.01M12 9h.01M16 9h.01"/></svg>
                            <span>Company Tagged</span>
                        </a>
                        <a class="{{ $activeMenu === 'cheatsheets' ? 'is-active' : '' }}" href="{{ $menuLink('cheatsheets', ['search' => 'array']) }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h4"/></svg>
                            <span>Cheatsheets</span>
                        </a>
                        <a class="{{ $activeMenu === 'quizzes' ? 'is-active' : '' }}" href="{{ $menuLink('quizzes') }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9.1 9a3 3 0 1 1 5.8 1c-.7 1.4-2.9 1.5-2.9 3.5M12 18h.01"/></svg>
                            <span>Quizzes</span>
                        </a>
                    </div>
                </section>
                <section class="practice-menu-section" aria-label="Learning tracks">
                    <a class="practice-menu__group {{ $activeMenu === 'ai' ? 'is-active' : '' }}" href="{{ $menuLink('ai', ['search' => 'ai']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v4m0 10v4m9-9h-4M7 12H3m15.07-6.07-2.83 2.83M8.76 15.24l-2.83 2.83m12.14 0-2.83-2.83M8.76 8.76 5.93 5.93"/></svg>
                        <span>AI Coding</span>
                        <em>Beta</em>
                    </a>
                    <a class="practice-menu__group {{ $activeMenu === 'system-design' ? 'is-active' : '' }}" href="{{ $menuLink('system-design', ['search' => 'system']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v18m8-13.5-8 4.5-8-4.5"/></svg>
                        <span>System Design</span>
                    </a>
                    <a class="practice-menu__group {{ $activeMenu === 'machine-learning' ? 'is-active' : '' }}" href="{{ $menuLink('machine-learning', ['search' => 'machine']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 7h12v10H6zM9 3v4m6-4v4M9 17v4m6-4v4M3 10h3m15 0h-3M3 14h3m15 0h-3"/></svg>
                        <span>Machine Learning</span>
                        <b aria-hidden="true">&gt;</b>
                    </a>
                    <a class="practice-menu__group {{ $activeMenu === 'databases' ? 'is-active' : '' }}" href="{{ $menuLink('databases', ['search' => 'database']) }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 7c0 2 14 2 14 0S5 5 5 7Zm0 0v10c0 2 14 2 14 0V7M5 12c0 2 14 2 14 0"/></svg>
                        <span>Databases</span>
                    </a>
                </section>
            </nav>
        </aside>

        <main class="practice-main">
            <section class="track-switcher" aria-label="Practice tracks">
                @forelse($featuredCourses as $course)
                    <a class="track-card track-card--{{ ($loop->index % 3) + 1 }}" href="{{ route('courses.show', $course) }}">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Zm-7 9 7 4 7-4M5 16l7 4 7-4"/></svg></span>
                        <strong>{{ $course->title }}</strong>
                    </a>
                @empty
                    <article class="track-card track-card--1">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Zm-7 9 7 4 7-4M5 16l7 4 7-4"/></svg></span>
                        <strong>Algorithms & Data Structures</strong>
                    </article>
                    <article class="track-card track-card--2">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5v14m-5-7a5 5 0 0 0 10 0 5 5 0 0 0-10 0Z"/></svg></span>
                        <strong>Advanced Algorithms</strong>
                    </article>
                    <article class="track-card track-card--3">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3c4 0 7 1.3 7 3s-3 3-7 3-7-1.3-7-3 3-3 7-3Zm-7 3v12c0 1.7 3 3 7 3s7-1.3 7-3V6M5 12c0 1.7 3 3 7 3s7-1.3 7-3"/></svg></span>
                        <strong>Python for Coding Interviews</strong>
                    </article>
                @endforelse
            </section>

            <section class="core-hero">
                <div>
                    <h1>{{ $trackTitle }}</h1>
                    <p>{{ $trackSummary }}</p>
                </div>
                <div class="core-stats" aria-label="Track stats">
                    <article><span>Solved</span><strong>{{ $solvedCount }}/{{ $totalProblems }}</strong></article>
                    <article><span>Published</span><strong>{{ $totalProblems }}</strong></article>
                    <article><span>Topics</span><strong>{{ $topics->count() }}</strong></article>
                </div>
            </section>

            <div class="practice-tabs" aria-label="Problem sets">
                <button class="active" type="button" data-topic-filter="">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 5h6v6H5zM13 5h6v6h-6zM5 13h6v6H5zM13 13h6v6h-6z"/></svg></span>
                    Core Skills
                </button>
                @if($topTopic)
                    <button type="button" data-topic-filter="{{ strtolower($topTopic->name) }}">
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5v14"/></svg></span>
                        {{ $topTopic->name }}
                    </button>
                @endif
                @foreach($topics->skip(1)->take(4) as $topic)
                    <button type="button" data-topic-filter="{{ strtolower($topic->name) }}">{{ $topic->name }}</button>
                @endforeach
            </div>

            <section class="problem-toolbar" aria-label="Problem filters">
                <form method="GET" class="practice-search">
                    @if($activeMenu !== 'problems')
                        <input type="hidden" name="menu" value="{{ $activeMenu }}">
                    @endif
                    <label class="search-field">
                        <span class="sr-only">Search problems</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.15a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
                        <input name="search" type="search" value="{{ $search }}" placeholder="Search problems" data-problem-search>
                    </label>
                    <label>
                        <span class="sr-only">Difficulty</span>
                        <select name="difficulty">
                            <option value="">Difficulty</option>
                            @foreach(\App\Models\Exercise::DIFFICULTIES as $option)
                                <option value="{{ $option }}" @selected($difficulty === $option)>{{ ucfirst(strtolower($option)) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="sr-only">Sort</span>
                        <select name="sort">
                            <option value="recent" @selected($sort === 'recent')>Recent</option>
                            <option value="difficulty" @selected($sort === 'difficulty')>Difficulty</option>
                        </select>
                    </label>
                    <button type="submit" class="button--secondary">Apply</button>
                </form>
                <div class="toolbar-icons">
                    <button type="button" class="toolbar-icon-button" data-random-problem aria-label="Choose random problem" aria-pressed="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 3h5v5M4 20 21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
                        <span role="tooltip" data-random-tooltip>Choose random problem</span>
                    </button>
                    <button type="button" class="toolbar-icon-button" aria-label="Delete progress">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"/></svg>
                        <span role="tooltip">Delete progress</span>
                    </button>
                    <button type="button" class="toolbar-icon-button" data-practice-about-open aria-label="What is this?" aria-haspopup="dialog" aria-controls="practice-about-modal">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4l3 2m5-2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                        <span role="tooltip">What is this?</span>
                    </button>
                </div>
            </section>

            <section class="practice-problem-list">
                <div class="practice-table" role="table" aria-label="Problems">
                    <div class="practice-table__head" role="row">
                        <span role="columnheader">Status</span>
                        <span role="columnheader">Problem</span>
                        <span role="columnheader">Difficulty</span>
                    </div>
                    @forelse($exercises as $exercise)
                        @php
                            $topicsText = $exercise->concepts->pluck('name')->join(', ') ?: 'General';
                            $isSolved = $exercise->user_accepted_count > 0;
                            $isAttempted = $exercise->user_attempts_count > 0;
                        @endphp
                        <article
                            class="practice-problem-row"
                            role="row"
                            data-problem-row
                            data-title="{{ strtolower($exercise->title.' '.$exercise->summary.' '.$topicsText.' '.$exercise->difficulty) }}"
                            data-difficulty="{{ $exercise->difficulty }}"
                            data-topic="{{ strtolower($topicsText) }}"
                        >
                            <span class="problem-status {{ $isSolved ? 'is-solved' : ($isAttempted ? 'is-attempted' : '') }}" role="cell" aria-label="{{ $isSolved ? 'Solved' : ($isAttempted ? 'Attempted' : 'Not attempted') }}"></span>
                            <span role="cell" class="practice-problem-title">
                                <a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->title }}</a>
                                <small>{{ $topicsText }}</small>
                            </span>
                            <span role="cell"><x-difficulty-badge :difficulty="$exercise->difficulty" /></span>
                        </article>
                    @empty
                        <div class="empty-state problem-empty"><p>No published exercises match this filter.</p></div>
                    @endforelse
                </div>
                <div class="empty-state problem-empty" data-no-problem-results hidden>
                    <p>No problems match that search.</p>
                    <button type="button" class="button button--secondary" data-clear-problem-search>Clear search</button>
                </div>
            </section>
        </main>

        <aside class="practice-sidebar" aria-label="Practice summary">
            <section class="practice-side-card progress-card">
                <h2>Core Skills</h2>
                <div class="progress-card__body">
                    <div class="difficulty-list">
                        @foreach(\App\Models\Exercise::DIFFICULTIES as $level)
                            <p class="difficulty-line difficulty-line--{{ strtolower($level) }}">
                                <span>{{ ucfirst(strtolower($level)) }}</span>
                                <strong>{{ $difficultySolved[$level] }}/{{ $difficultyTotals[$level] }}</strong>
                            </p>
                        @endforeach
                    </div>
                    <div class="progress-gauge" style="--solve-percent: {{ $solvedPercent }}%">
                        <strong>{{ $solvedCount }}</strong>
                        <span>/{{ $totalProblems }} Solved</span>
                    </div>
                </div>
            </section>

            <section class="practice-side-card dark-calendar-card">
                <header class="dark-calendar__header">
                    <a href="{{ route('catalog.index', array_merge($calendarQuery, ['month' => $monthCalendar['prev_month']])) }}" aria-label="Previous month">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <strong>{{ $monthCalendar['label'] }}</strong>
                    <a href="{{ route('catalog.index', array_merge($calendarQuery, ['month' => $monthCalendar['next_month']])) }}" aria-label="Next month">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </header>
                <div class="dark-calendar__meta">
                    <strong>Day {{ $todayLocal->day }}</strong>
                    <span data-calendar-countdown data-reset-at="{{ $resetAt }}">{{ $todayLocal->diff($todayLocal->copy()->endOfDay())->format('%H:%I:%S') }} left</span>
                </div>
                <div class="dark-calendar__weekdays" aria-hidden="true">
                    @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $weekday)
                        <span>{{ $weekday }}</span>
                    @endforeach
                </div>
                <div class="dark-calendar__days" aria-label="{{ $monthCalendar['label'] }} activity">
                    @foreach($monthCalendar['days'] as $day)
                        <span
                            class="{{ $day['in_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['answer_count'] > 0 ? 'has-activity' : '' }}"
                            title="{{ $day['date'] }}: {{ $day['answer_count'] }} answers"
                        >{{ $day['day'] }}</span>
                    @endforeach
                </div>
                <div class="streak-tiles">
                    <article>
                        <span>Current Streak</span>
                        <strong>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22c4 0 7-2.7 7-6.8 0-2.7-1.6-5.1-3.4-6.9-.3 2.1-1.4 3.5-2.8 4.2.5-3.6-.9-6.7-3.4-9.5-.2 3.1-1.9 5.2-3.1 6.7C5.4 11 5 12.6 5 15.2 5 19.3 8 22 12 22Z"/></svg>
                            {{ $streak->current_streak }} {{ $streak->current_streak === 1 ? 'day' : 'days' }}
                        </strong>
                    </article>
                    <article>
                        <span>Best Streak</span>
                        <strong>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Zm10 2h3v2a3 3 0 0 1-3 3M7 6H4v2a3 3 0 0 0 3 3"/></svg>
                            {{ $streak->longest_streak }} {{ $streak->longest_streak === 1 ? 'day' : 'days' }}
                        </strong>
                    </article>
                </div>
                <div class="daily-goal-strip" style="--daily-goal-progress: {{ $dailyGoalProgress }}%" aria-label="{{ $acceptedToday }} of {{ $dailyGoal }} daily solves">
                    <strong><span aria-hidden="true">♥</span>{{ $acceptedToday }}</strong>
                    <div>
                        <span>{{ $acceptedToday }}/{{ $dailyGoal }} to next</span>
                        <i aria-hidden="true"></i>
                    </div>
                </div>
                <p>Solve one problem a day to keep your streak.</p>
            </section>

            <section class="practice-side-card ranking-card">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 17h8M9 13l3-3 3 3M12 10v10M5 4h14"/></svg>
                <p>Keep solving to climb your ranking.</p>
            </section>
        </aside>

        <div class="practice-about-modal" id="practice-about-modal" data-practice-about-modal hidden role="dialog" aria-modal="true" aria-labelledby="practice-about-title">
            <div class="practice-about-modal__backdrop" data-practice-about-close></div>
            <section class="practice-about-modal__card" role="document">
                <header>
                    <h2 id="practice-about-title">About</h2>
                    <button type="button" class="icon-button" data-practice-about-close aria-label="Close about popup">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </header>
                <div class="practice-about-modal__body">
                    <p>Hi, I created Coddy to make coding practice easier and more focused.</p>
                    <ul>
                        <li><strong>Core Skills</strong> is a beginner friendly list of coding practice problems.</li>
                        <li><strong>Courses</strong> help you learn lessons step by step before solving exercises.</li>
                        <li><strong>Problems</strong> let you write code, run visible tests, and submit your answer.</li>
                        <li>Use the random button when you want the system to choose your next challenge.</li>
                    </ul>
                </div>
            </section>
        </div>
    </section>
@endsection
