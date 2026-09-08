@extends('layouts.app')

@section('content')
    <article class="stack lesson-page">
        <header class="page-heading"><div><p class="lesson-breadcrumb"><a href="{{ route('courses.show', $lesson->course) }}">{{ $lesson->course->title }}</a><span aria-hidden="true">/</span> Lesson</p><h1>{{ $lesson->title }}</h1><p>{{ $lesson->summary }}</p></div></header>
        <div class="prose panel">{!! nl2br(e($lesson->body_markdown)) !!}</div>
        @if($lesson->code_example)
            <section class="panel stack code-panel"><div class="section-heading"><h2>Example</h2><span class="code-meta">Code</span></div><pre><code>{{ $lesson->code_example }}</code></pre></section>
        @endif
        <form method="POST" action="{{ route('lessons.complete', $lesson) }}" class="lesson-complete">
            @csrf
            <button type="submit">Mark lesson complete <span aria-hidden="true">✓</span></button>
        </form>

        <section class="panel stack"><div class="section-heading"><div><p class="eyebrow">Apply this lesson</p><h2>Practice</h2></div></div>
            @forelse($lesson->exercises as $exercise)
                <a class="list-item" href="{{ route('exercises.show', $exercise) }}"><span><strong>{{ $exercise->title }}</strong><small>{{ $exercise->summary }}</small></span><x-difficulty-badge :difficulty="$exercise->difficulty" /></a>
            @empty
                <div class="empty-state"><span aria-hidden="true">⌁</span><p>No practice exercise is attached yet.</p></div>
            @endforelse
        </section>
    </article>
@endsection
