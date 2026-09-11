@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin · course structure</p>
        <h1>{{ $module->exists ? 'Edit module' : 'New module' }}</h1>
        <form method="POST" action="{{ $module->exists ? route('admin.modules.update', $module) : route('admin.modules.store') }}" class="panel stack">
            @csrf
            @if($module->exists)
                @method('PATCH')
            @endif
            <label>Course
                <select name="course_id" required>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id', $module->course_id) === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>Title <input name="title" value="{{ old('title', $module->title) }}" required></label>
            <label>Slug <input name="slug" value="{{ old('slug', $module->slug) }}"></label>
            <label>Summary <input name="summary" value="{{ old('summary', $module->summary) }}"></label>
            <label>Sort order <input name="sort_order" type="number" min="1" value="{{ old('sort_order', $module->sort_order ?: 1) }}" required></label>
            <button type="submit">Save module</button>
        </form>
    </section>
@endsection
