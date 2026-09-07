@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Course</p>
        <h1>{{ $course->title }}</h1>
        <p class="lead">{{ $course->summary }}</p>
        <form method="POST" action="{{ route('courses.enroll', $course) }}" class="actions">
            @csrf
            <button type="submit">Enroll</button>
        </form>

        <div class="grid-two">
            <section class="panel stack">
                <h2>Lessons</h2>
                @forelse($course->lessons as $lesson)
                    <p><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a><br><span class="muted">{{ $lesson->summary }}</span></p>
                @empty
                    <p>No published lessons yet.</p>
                @endforelse
            </section>

            <section class="panel stack">
                <h2>Exercises</h2>
                @forelse($course->exercises as $exercise)
                    <p><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->title }}</a> <span class="badge">{{ $exercise->difficulty }}</span><br><span class="muted">{{ $exercise->summary }}</span></p>
                @empty
                    <p>No published exercises yet.</p>
                @endforelse
            </section>
        </div>
    </section>
@endsection
