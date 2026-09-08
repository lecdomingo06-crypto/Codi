@extends('layouts.app')

@section('content')
    <section class="stack course-page">
        <header class="page-heading course-heading">
            <div><p class="eyebrow">Learning path</p><h1>{{ $course->title }}</h1><p>{{ $course->summary }}</p></div>
            <form method="POST" action="{{ route('courses.enroll', $course) }}" class="actions">
            @csrf
            <button type="submit">Enroll in course <span aria-hidden="true">→</span></button>
            </form>
        </header>

        <div class="grid-two">
            <section class="panel stack">
                <div class="section-heading"><h2>Lessons</h2><span class="count-label">{{ $course->lessons->count() }}</span></div>
                @forelse($course->lessons as $lesson)
                    <a class="list-item" href="{{ route('lessons.show', $lesson) }}"><span class="list-item__icon" aria-hidden="true">▤</span><span><strong>{{ $lesson->title }}</strong><small>{{ $lesson->summary }}</small></span><span aria-hidden="true">→</span></a>
                @empty
                    <p>No published lessons yet.</p>
                @endforelse
            </section>

            <section class="panel stack">
                <div class="section-heading"><h2>Exercises</h2><span class="count-label">{{ $course->exercises->count() }}</span></div>
                @forelse($course->exercises as $exercise)
                    <a class="list-item" href="{{ route('exercises.show', $exercise) }}"><span><strong>{{ $exercise->title }}</strong><small>{{ $exercise->summary }}</small></span><x-difficulty-badge :difficulty="$exercise->difficulty" /></a>
                @empty
                    <p>No published exercises yet.</p>
                @endforelse
            </section>
        </div>
    </section>
@endsection
