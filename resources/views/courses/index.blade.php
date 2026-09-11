@extends('layouts.app')

@section('content')
    <section class="stack courses-page">
        <header class="page-heading courses-hero">
            <div>
                <p class="eyebrow">Structured learning</p>
                <h1>Courses</h1>
                <p>Follow a clear path from first principles to confident problem solving.</p>
            </div>
            <span class="count-label">{{ $courses->count() }} courses</span>
        </header>

        @forelse($courses->groupBy('category') as $category => $categoryCourses)
            <section class="course-category stack">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ $categoryCourses->count() }} {{ $categoryCourses->count() === 1 ? 'course' : 'courses' }}</p>
                        <h2>{{ $category }}</h2>
                        <p>{{ $categoryDescriptions[$category] ?? 'Explore a focused set of lessons and exercises.' }}</p>
                    </div>
                </div>

                <div class="courses-grid">
                    @foreach($categoryCourses as $course)
                        <article class="course-tile">
                            <a class="course-tile__visual" href="{{ route('courses.show', $course) }}" aria-label="Open {{ $course->title }}">
                                <span class="course-tile__glyph" aria-hidden="true">{{ strtoupper(substr($course->title, 0, 2)) }}</span>
                                <span class="course-tile__category">{{ $category }}</span>
                            </a>
                            <div class="course-tile__body">
                                <h3><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
                                <p>{{ $course->summary }}</p>
                                <div class="course-tile__meta">
                                    <span>{{ $course->duration_minutes ? $course->duration_minutes . ' min' : 'Self-paced' }}</span>
                                    <span>{{ ucfirst(strtolower($course->difficulty)) }}</span>
                                    <span>{{ $course->published_lessons_count }} lessons</span>
                                </div>
                                @if($course->published_lessons_count > 0)
                                    <div class="course-progress" aria-label="{{ $course->progress_percent }} percent complete">
                                        <div class="course-progress__label"><span>Progress</span><strong>{{ $course->progress_percent }}%</strong></div>
                                        <div class="course-progress__track"><span style="width: {{ $course->progress_percent }}%"></span></div>
                                        <small>{{ $course->completed_lessons_count }} / {{ $course->published_lessons_count }} lessons completed</small>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state"><p>No published courses are available yet.</p></div>
        @endforelse
    </section>
@endsection