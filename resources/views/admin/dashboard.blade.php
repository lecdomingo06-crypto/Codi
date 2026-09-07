@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Content operations</p><h1>Admin dashboard</h1><p>Manage the learning library and keep an eye on platform activity.</p></div></header>
        <p class="actions">
            <a class="button" href="{{ route('admin.courses.index') }}">Courses</a>
            <a class="button" href="{{ route('admin.lessons.index') }}">Lessons</a>
            <a class="button" href="{{ route('admin.exercises.index') }}">Exercises</a>
            <a class="button secondary" href="{{ route('admin.exercises.import.form') }}">Import exercises</a>
        </p>
        <section class="stats-section" aria-label="Platform overview">
            <div class="section-heading"><h2>Platform overview</h2></div>
            <div class="stats-list stats-list--five">
                <div><span>Courses</span><strong>{{ $courseCount }}</strong></div>
                <div><span>Published problems</span><strong>{{ $publishedExerciseCount }}</strong></div>
                <div><span>Draft problems</span><strong>{{ $draftExerciseCount }}</strong></div>
                <div><span>Submissions</span><strong>{{ $submissionCount }}</strong></div>
                <div><span>Learners</span><strong>{{ $userCount }}</strong></div>
            </div>
        </section>
        <section class="content-section stack">
            <div class="section-heading"><div><p class="eyebrow">Live activity</p><h2>Recent submissions</h2></div></div>
            @forelse($recentSubmissions as $submission)
                <div class="submission-item"><span class="status-dot {{ $submission->verdict === 'ACCEPTED' ? 'status-dot--accepted' : 'status-dot--failed' }}"></span><span><strong>{{ $submission->exercise->title }}</strong><small>{{ $submission->created_at->format('M j, Y · H:i') }}</small></span><x-verdict-badge :verdict="$submission->verdict" /></div>
            @empty
                <div class="empty-state"><span aria-hidden="true">⌘</span><p>No submissions yet.</p></div>
            @endforelse
        </section>
    </section>
@endsection
