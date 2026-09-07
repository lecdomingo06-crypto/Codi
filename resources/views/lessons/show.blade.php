@extends('layouts.app')

@section('content')
    <article class="stack">
        <p><a href="{{ route('courses.show', $lesson->course) }}">{{ $lesson->course->title }}</a></p>
        <h1>{{ $lesson->title }}</h1>
        <p>{{ $lesson->summary }}</p>
        <div class="prose">{!! nl2br(e($lesson->body_markdown)) !!}</div>
        @if($lesson->code_example)
            <pre><code>{{ $lesson->code_example }}</code></pre>
        @endif
        <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
            @csrf
            <button type="submit">Mark complete</button>
        </form>

        <h2>Practice</h2>
        @forelse($lesson->exercises as $exercise)
            <p><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->title }}</a> &middot; {{ $exercise->difficulty }}</p>
        @empty
            <p>No practice exercise attached yet.</p>
        @endforelse
    </article>
@endsection
