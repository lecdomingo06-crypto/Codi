@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>{{ $course->exists ? 'Edit course' : 'New course' }}</h1>
        <form method="POST" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}" class="panel stack">
            @csrf
            @if($course->exists)
                @method('PATCH')
            @endif
            <label>Title <input name="title" value="{{ old('title', $course->title) }}" required></label>
            <label>Slug <input name="slug" value="{{ old('slug', $course->slug) }}"></label>
            <label>Summary <input name="summary" value="{{ old('summary', $course->summary) }}" required></label>
            <label>Category <input name="category" value="{{ old('category', $course->category ?: 'Programming') }}" required></label>
            <label>Estimated duration in minutes <input name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $course->duration_minutes) }}"></label>
            <label>Difficulty
                <select name="difficulty">
                    @foreach(['EASY', 'MEDIUM', 'HARD'] as $difficulty)
                        <option value="{{ $difficulty }}" @selected(old('difficulty', $course->difficulty ?: 'EASY') === $difficulty)>{{ $difficulty }}</option>
                    @endforeach
                </select>
            </label>
            <label>Description <textarea name="description_markdown" rows="8">{{ old('description_markdown', $course->description_markdown) }}</textarea></label>
            <label>Status
                <select name="publication_status">
                    @foreach(['DRAFT', 'PUBLISHED', 'ARCHIVED'] as $status)
                        <option value="{{ $status }}" @selected(old('publication_status', $course->publication_status ?: 'DRAFT') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Save course</button>
        </form>
    </section>
@endsection
