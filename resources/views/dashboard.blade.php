@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Learner dashboard</p>
        <h1>Welcome back, {{ $user->name }}.</h1>
        <div class="metrics">
            <p><strong>{{ $user->streak?->current_streak ?? 0 }}</strong><span>current streak</span></p>
            <p><strong>{{ $user->streak?->longest_streak ?? 0 }}</strong><span>longest streak</span></p>
            <p><strong>{{ $user->points }}</strong><span>points</span></p>
            <p><strong>{{ $availableCourses }}</strong><span>published courses</span></p>
        </div>

        <div class="grid-two">
            <section class="panel stack">
                <h2>Recommended next</h2>
                @if($recommendation)
                    <p><strong>{{ $recommendation->title }}</strong> <span class="badge">{{ $recommendation->difficulty }}</span></p>
                    <p>{{ $recommendation->summary }}</p>
                    <a class="button" href="{{ route('exercises.show', $recommendation) }}">Continue</a>
                @else
                    <p>All published exercises are complete.</p>
                @endif
            </section>

            <section class="panel stack">
                <h2>Enrollments</h2>
                @forelse($enrollments as $enrollment)
                    <p><a href="{{ route('courses.show', $enrollment->course) }}">{{ $enrollment->course->title }}</a></p>
                @empty
                    <p>No enrollments yet.</p>
                    <a class="button secondary" href="{{ route('catalog.index') }}">Find a course</a>
                @endforelse
            </section>
        </div>

        <section class="panel stack">
            <h2>Recent submissions</h2>
            @forelse($recentSubmissions as $submission)
                <p><a href="{{ route('submissions.show', $submission) }}">{{ $submission->exercise->title }}</a> &middot; {{ $submission->verdict }}</p>
            @empty
                <p>No submissions yet.</p>
            @endforelse
        </section>

        @include('shared.activity-calendar', ['calendar' => $calendar])
    </section>
@endsection
