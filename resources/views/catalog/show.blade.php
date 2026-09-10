@extends('layouts.app')

@section('content')
    <section class="stack course-page">
        <header class="page-heading course-heading">
            <div><p class="eyebrow">Learning path</p><h1>{{ $course->title }}</h1><p>{{ $course->summary }}</p></div>
            <div class="actions">
                @if(!$isAdmin)
                    @if(!$isEnrolled)
                        <form method="POST" action="{{ route('courses.enroll', $course) }}">
                            @csrf
                            <button type="submit">Enroll in course <span aria-hidden="true">→</span></button>
                        </form>
                    @else
                        <span class="badge">Enrolled</span>
                    @endif
                @endif
            </div>
        </header>

        <div class="grid-two">
            <section class="panel stack">
                <div class="section-heading"><h2>Modules</h2><span class="count-label">{{ $course->modules->count() }}</span></div>
                @forelse($course->modules as $module)
                    <div class="module-block">
                        <div class="section-heading"><div><h3>{{ $module->title }}</h3><p>{{ $module->summary }}</p></div><span class="count-label">{{ $module->lessons->count() }} lessons</span></div>
                        @foreach($module->lessons as $lesson)
                            <a class="list-item" href="{{ route('lessons.show', $lesson) }}"><span class="list-item__icon" aria-hidden="true">▤</span><span><strong>{{ $lesson->title }}</strong><small>{{ $lesson->summary }}</small></span><span aria-hidden="true">→</span></a>
                        @endforeach
                    </div>
                @empty
                    @forelse($course->lessons as $lesson)
                        <a class="list-item" href="{{ route('lessons.show', $lesson) }}"><span class="list-item__icon" aria-hidden="true">▤</span><span><strong>{{ $lesson->title }}</strong><small>{{ $lesson->summary }}</small></span><span aria-hidden="true">→</span></a>
                    @empty
                        <p>No published lessons yet.</p>
                    @endforelse
                @endforelse
            </section>

            <aside class="course-sidebar stack">
                @if(!$isAdmin && $course->published_lessons_count > 0)
                    <section class="panel course-progress stack">
                        <div class="course-progress__label"><span>Course progress</span><strong>{{ $course->progress_percent }}%</strong></div>
                        <div class="course-progress__track"><span style="width: {{ $course->progress_percent }}%"></span></div>
                        <p>{{ $course->completed_lessons_count }} / {{ $course->published_lessons_count }} lessons completed</p>
                    </section>
                @endif

                <section class="panel stack">
                    <div class="section-heading"><h2>Exercises</h2><span class="count-label">{{ $course->exercises->count() }}</span></div>
                    @forelse($course->exercises as $exercise)
                        <a class="list-item" href="{{ route('exercises.show', $exercise) }}"><span><strong>{{ $exercise->title }}</strong><small>{{ $exercise->summary }}</small></span><x-difficulty-badge :difficulty="$exercise->difficulty" /></a>
                    @empty
                        <p>No published exercises yet.</p>
                    @endforelse
                </section>
            </aside>
        </div>
    </section>
@endsection
