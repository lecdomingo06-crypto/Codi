@extends('layouts.app')

@section('content')
    <section class="stack problems-page" data-problem-browser>
        <header class="page-heading">
            <div>
                <p class="eyebrow">Practice library</p>
                <h1>Problems</h1>
                <p>Practice programming problems and build durable problem-solving skills.</p>
            </div>
        </header>
        <form method="GET" class="filters problem-filters">
            <label class="search-field"><span class="sr-only">Search problems</span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.15a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg><input name="search" type="search" value="{{ request('search') }}" placeholder="Search a problem or topic" data-problem-search></label>
            <label>Difficulty
                <select name="difficulty">
                    <option value="">All</option>
                    @foreach(\App\Models\Exercise::DIFFICULTIES as $option)
                        <option value="{{ $option }}" @selected($difficulty === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label>Sort
                <select name="sort">
                    <option value="recent" @selected($sort === 'recent')>Recent</option>
                    <option value="difficulty" @selected($sort === 'difficulty')>Difficulty</option>
                </select>
            </label>
            <button type="submit" class="button--secondary">Apply filters</button>
        </form>

        <section class="stack">
            <div class="section-heading"><div><p class="eyebrow">Guided paths</p><h2>Courses</h2></div><span class="count-label">{{ $courses->count() }} available</span></div>
            <div class="catalog-grid">
                @forelse($courses as $course)
                    <article class="panel course-card stack">
                        <span class="course-card__icon" aria-hidden="true">▤</span>
                        <h3><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
                        <p>{{ $course->summary }}</p>
                        <p class="card-meta">{{ $course->lessons_count }} lessons <span>·</span> {{ $course->exercises_count }} exercises</p>
                    </article>
                @empty
                    <p class="empty-copy">No published courses yet.</p>
                @endforelse
            </div>
        </section>

        <section class="stack problem-list-section">
            <div class="section-heading"><div><p class="eyebrow">Coding challenges</p><h2>All problems</h2></div><span class="count-label" data-problem-count>{{ $exercises->count() }} problems</span></div>
            <div class="problem-table" role="table" aria-label="Problems">
                <div class="problem-table__head" role="row"><span role="columnheader">Status</span><span role="columnheader">#</span><span role="columnheader">Title</span><span role="columnheader">Difficulty</span><span role="columnheader">Topics</span><span role="columnheader">Action</span></div>
                @forelse($exercises as $exercise)
                    <article class="problem-row" role="row" data-problem-row data-title="{{ strtolower($exercise->title.' '.$exercise->summary.' '.$exercise->concepts->pluck('name')->join(' ')) }}" data-difficulty="{{ $exercise->difficulty }}">
                        <span class="problem-status" role="cell" aria-label="Not attempted"></span>
                        <span class="problem-number" role="cell">{{ $loop->iteration }}</span>
                        <span role="cell" class="problem-title"><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->title }}</a><small>{{ $exercise->summary }}</small></span>
                        <span role="cell"><x-difficulty-badge :difficulty="$exercise->difficulty" /></span>
                        <span role="cell" class="topic-list">{{ $exercise->concepts->pluck('name')->join(', ') ?: 'General' }}</span>
                        <span role="cell"><a class="row-action" href="{{ route('exercises.show', $exercise) }}">Solve <span aria-hidden="true">→</span></a></span>
                    </article>
                @empty
                    <div class="empty-state problem-empty"><span aria-hidden="true">⌘</span><p>No published exercises match this filter.</p></div>
                @endforelse
            </div>
            <div class="empty-state problem-empty" data-no-problem-results hidden><span aria-hidden="true">⌕</span><p>No problems match that search.</p><button type="button" class="button button--secondary" data-clear-problem-search>Clear search</button></div>
        </section>
    </section>
@endsection
