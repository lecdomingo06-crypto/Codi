@extends('layouts.app')

@php
    $courseGroups = $courses->groupBy('category');
    $categoryCopy = [
        'Data Structures & Algorithms' => 'Follow a structured path to learn all of the core data structures and algorithms. Perfect for coding interview preparation.',
        'System Design' => 'Brush up on core system design concepts for designing robust backend systems.',
        'Python' => 'Learn Python syntax, data handling, and practical patterns for solving coding problems.',
        'Web Development' => 'Build the web foundations behind interactive products and server-rendered applications.',
        'Programming' => 'Strengthen core programming habits with focused lessons and practice.',
    ];

    $coverClass = function (?string $category, int $index): string {
        $key = strtolower((string) $category);

        if (str_contains($key, 'data') || str_contains($key, 'algorithm')) {
            return $index % 2 === 0 ? 'cover-dsa' : 'cover-algorithms';
        }

        if (str_contains($key, 'system')) {
            return $index % 2 === 0 ? 'cover-system' : 'cover-cloud';
        }

        if (str_contains($key, 'python')) {
            return 'cover-python';
        }

        if (str_contains($key, 'web')) {
            return 'cover-web';
        }

        return 'cover-default';
    };

    $lessonCoverClass = function ($lesson, int $index) use ($coverClass): string {
        return $coverClass($lesson->course?->category, $index);
    };
@endphp

@section('content')
    <section class="neet-courses-shell">
        <aside class="neet-courses-sidebar" aria-label="Courses navigation">
            <a class="is-active" href="#courses">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m4 8 8-4 8 4-8 4-8-4Zm3 3.5v4L12 18l5-2.5v-4"/></svg>
                Courses
            </a>
            <a href="#lessons">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 5 10 7-10 7V5Z"/></svg>
                Lessons
            </a>
        </aside>

        <main class="neet-courses-main">
            <section class="neet-courses-section" id="courses">
                <header class="neet-page-title">
                    <h1>Courses<span aria-hidden="true">.</span></h1>
                    @if($search !== '')
                        <p>Search results for "{{ $search }}"</p>
                    @endif
                </header>

                @forelse($courseGroups as $category => $categoryCourses)
                    <section class="neet-course-group">
                        <header class="neet-section-heading">
                            <div>
                                <h2>{{ $category }}</h2>
                                <p>{{ $categoryCopy[$category] ?? ($categoryDescriptions[$category] ?? 'Explore a focused set of lessons and exercises.') }}</p>
                            </div>
                            <span>{{ $categoryCourses->count() }} {{ $categoryCourses->count() === 1 ? 'course' : 'courses' }}</span>
                        </header>

                        <div class="neet-card-grid neet-card-grid--courses">
                            @foreach($categoryCourses->values() as $index => $course)
                                <article class="neet-course-card">
                                    <a class="neet-cover {{ $coverClass($category, $index) }}" href="{{ route('courses.show', $course) }}" aria-label="Open {{ $course->title }}">
                                        <span class="neet-cover__label">{{ $course->title }}</span>
                                        <span class="neet-cover__art" aria-hidden="true"></span>
                                    </a>
                                    <div class="neet-card-body">
                                        <h3><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
                                        <p>{{ $course->summary }}</p>
                                        <div class="neet-card-meta">
                                            <span>
                                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                {{ $course->duration_minutes ? round($course->duration_minutes / 60, 1) . ' hours' : 'Self-paced' }}
                                            </span>
                                            <b class="neet-difficulty neet-difficulty--{{ strtolower($course->difficulty ?: 'easy') }}">{{ ucfirst(strtolower($course->difficulty ?: 'easy')) }}</b>
                                        </div>
                                        @if($course->published_lessons_count > 0)
                                            <div class="neet-progress" aria-label="{{ $course->progress_percent }} percent complete">
                                                <span><b style="width: {{ $course->progress_percent }}%"></b></span>
                                                <small>{{ $course->completed_lessons_count }} / {{ $course->published_lessons_count }} lessons complete</small>
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

            <section class="neet-courses-section neet-lessons-section" id="lessons">
                <header class="neet-page-title">
                    <h1>Lessons<span aria-hidden="true">.</span></h1>
                </header>
                <section class="neet-course-group">
                    <header class="neet-section-heading">
                        <div>
                            <h2>All Lessons</h2>
                            <p>Jump straight into short lessons from every published course.</p>
                        </div>
                        <span>{{ $lessons->count() }} {{ $lessons->count() === 1 ? 'lesson' : 'lessons' }}</span>
                    </header>

                    <div class="neet-card-grid neet-card-grid--lessons">
                        @forelse($lessons as $index => $lesson)
                            <article class="neet-lesson-card">
                                <a class="neet-cover {{ $lessonCoverClass($lesson, $index) }}" href="{{ route('lessons.show', $lesson) }}" aria-label="Open {{ $lesson->title }}">
                                    <span class="neet-cover__label">{{ $lesson->title }}</span>
                                    <span class="neet-cover__art" aria-hidden="true"></span>
                                </a>
                                <div class="neet-card-body">
                                    <h3><a href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a></h3>
                                    <p>{{ $lesson->course?->title ?? 'Course lesson' }}</p>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state"><p>No published lessons are available yet.</p></div>
                        @endforelse
                    </div>
                </section>
            </section>
        </main>
    </section>
@endsection
