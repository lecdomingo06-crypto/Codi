@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>{{ $lesson->exists ? 'Edit lesson' : 'New lesson' }}</h1>
        <form method="POST" action="{{ $lesson->exists ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" class="panel stack">
            @csrf
            @if($lesson->exists)
                @method('PATCH')
            @endif
            <label>Course
                <select name="course_id" required>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id', $lesson->course_id) === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>Module
                <select name="module_id">
                    <option value="">No module</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}" @selected((string) old('module_id', $lesson->module_id) === (string) $module->id)>{{ $module->course->title }} · {{ $module->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>Title <input name="title" value="{{ old('title', $lesson->title) }}" required></label>
            <label>Slug <input name="slug" value="{{ old('slug', $lesson->slug) }}"></label>
            <label>Summary <input name="summary" value="{{ old('summary', $lesson->summary) }}" required></label>
            <label>Video URL <input name="video_url" type="url" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://www.youtube.com/watch?v=..."></label>
            <label>Duration in minutes <input name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $lesson->duration_minutes) }}"></label>
            <label>Body <textarea name="body_markdown" rows="10" required>{{ old('body_markdown', $lesson->body_markdown) }}</textarea></label>
            <label>Code example <textarea name="code_example" rows="8">{{ old('code_example', $lesson->code_example) }}</textarea></label>
            <label>Sort order <input name="sort_order" type="number" min="1" value="{{ old('sort_order', $lesson->sort_order ?: 1) }}" required></label>
            <label>Status
                <select name="publication_status">
                    @foreach(['DRAFT', 'PUBLISHED', 'ARCHIVED'] as $status)
                        <option value="{{ $status }}" @selected(old('publication_status', $lesson->publication_status ?: 'DRAFT') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Save lesson</button>
        </form>
    </section>
@endsection
