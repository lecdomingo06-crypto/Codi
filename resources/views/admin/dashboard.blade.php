@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Content operations</p>
        <h1>Admin dashboard</h1>
        <p class="actions">
            <a class="button" href="{{ route('admin.courses.index') }}">Courses</a>
            <a class="button" href="{{ route('admin.lessons.index') }}">Lessons</a>
            <a class="button" href="{{ route('admin.exercises.index') }}">Exercises</a>
            <a class="button secondary" href="{{ route('admin.exercises.import.form') }}">Import exercises</a>
        </p>
        <div class="metrics">
            <p><strong>{{ $courseCount }}</strong><span>courses</span></p>
            <p><strong>{{ $publishedExerciseCount }}</strong><span>published exercises</span></p>
            <p><strong>{{ $draftExerciseCount }}</strong><span>draft exercises</span></p>
            <p><strong>{{ $submissionCount }}</strong><span>submissions</span></p>
            <p><strong>{{ $userCount }}</strong><span>learners</span></p>
        </div>
        <section class="panel stack">
            <h2>Recent submissions</h2>
            @forelse($recentSubmissions as $submission)
                <p>{{ $submission->exercise->title }} &middot; {{ $submission->verdict }} &middot; {{ $submission->created_at->format('Y-m-d H:i') }}</p>
            @empty
                <p>No submissions yet.</p>
            @endforelse
        </section>
    </section>
@endsection
