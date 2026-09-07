@extends('layouts.app')

@section('content')
    <section class="stack dashboard">
        <header class="page-heading dashboard-heading">
            <div>
                <p class="eyebrow">Your workspace</p>
                <h1>Welcome back, {{ strtok($user->name, ' ') }}.</h1>
                <p>Keep your momentum going with one focused problem at a time.</p>
            </div>
            <a class="button" href="{{ route('catalog.index') }}">Browse problems <span aria-hidden="true">→</span></a>
        </header>
        <section class="stats-section" aria-label="Practice overview">
            <div class="section-heading"><h2>Practice overview</h2><span class="count-label">Your current account data</span></div>
            <div class="stats-list">
                <div><span>Current streak</span><strong>{{ $user->streak?->current_streak ?? 0 }} days</strong></div>
                <div><span>Longest streak</span><strong>{{ $user->streak?->longest_streak ?? 0 }} days</strong></div>
                <div><span>Points earned</span><strong>{{ $user->points }}</strong></div>
                <div><span>Available courses</span><strong>{{ $availableCourses }}</strong></div>
            </div>
        </section>

        <div class="grid-two">
            <section class="content-section stack recommendation-card">
                <div class="section-heading"><div><p class="eyebrow">Continue learning</p><h2>Recommended next</h2></div></div>
                @if($recommendation)
                    <p class="recommendation-card__title"><strong>{{ $recommendation->title }}</strong> <x-difficulty-badge :difficulty="$recommendation->difficulty" /></p>
                    <p>{{ $recommendation->summary }}</p>
                    <a class="button" href="{{ route('exercises.show', $recommendation) }}">Solve problem <span aria-hidden="true">→</span></a>
                @else
                    <p>All published exercises are complete. Great work.</p>
                @endif
            </section>

            <section class="content-section stack">
                <div class="section-heading"><h2>Your courses</h2><a href="{{ route('catalog.index') }}">View all</a></div>
                @forelse($enrollments as $enrollment)
                    <a class="list-item" href="{{ route('courses.show', $enrollment->course) }}"><span class="list-item__icon" aria-hidden="true">▤</span><span><strong>{{ $enrollment->course->title }}</strong><small>Continue course</small></span><span aria-hidden="true">→</span></a>
                @empty
                    <div class="empty-state"><span aria-hidden="true">⌁</span><p>No courses yet. Pick a path to get started.</p><a class="button button--secondary" href="{{ route('catalog.index') }}">Explore courses</a></div>
                @endforelse
            </section>
        </div>

        <section class="content-section stack">
            <div class="section-heading"><div><p class="eyebrow">Activity</p><h2>Recent submissions</h2></div><a href="{{ route('progress.show') }}">View progress</a></div>
            @forelse($recentSubmissions as $submission)
                <a class="submission-item" href="{{ route('submissions.show', $submission) }}"><span class="status-dot {{ $submission->verdict === 'ACCEPTED' ? 'status-dot--accepted' : 'status-dot--failed' }}" aria-hidden="true"></span><span><strong>{{ $submission->exercise->title }}</strong><small>{{ $submission->submitted_at?->diffForHumans() ?? $submission->created_at->diffForHumans() }}</small></span><x-verdict-badge :verdict="$submission->verdict" /></a>
            @empty
                <div class="empty-state"><span aria-hidden="true">⌘</span><p>Your submitted solutions will appear here.</p><a href="{{ route('catalog.index') }}">Find a problem</a></div>
            @endforelse
        </section>

        @include('shared.activity-calendar', ['calendar' => $calendar])
    </section>
@endsection
