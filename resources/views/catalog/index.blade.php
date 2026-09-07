@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Published learning content</p>
        <h1>Catalog</h1>
        <form method="GET" class="filters">
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
            <button type="submit">Apply</button>
        </form>

        <section class="stack">
            <h2>Courses</h2>
            <div class="catalog-grid">
                @forelse($courses as $course)
                    <article class="panel stack">
                        <h3><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h3>
                        <p>{{ $course->summary }}</p>
                        <p>{{ $course->lessons_count }} lessons &middot; {{ $course->exercises_count }} exercises</p>
                    </article>
                @empty
                    <p>No published courses yet.</p>
                @endforelse
            </div>
        </section>

        <section class="stack">
            <h2>Exercises</h2>
            <div class="catalog-grid">
                @forelse($exercises as $exercise)
                    <article class="panel stack">
                        <h3><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->title }}</a> <span class="badge">{{ $exercise->difficulty }}</span></h3>
                        <p>{{ $exercise->summary }}</p>
                        <p>{{ $exercise->concepts->pluck('name')->join(', ') }}</p>
                    </article>
                @empty
                    <p>No published exercises match this filter.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection
