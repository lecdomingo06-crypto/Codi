@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · learning paths</p><h1>Courses</h1><p>Organize lessons and exercises into guided learning paths.</p></div></header>
        <p class="actions"><a class="button" href="{{ route('admin.courses.create') }}">New course</a></p>
        <div class="catalog-grid">
            @foreach($courses as $course)
            <article class="panel stack">
                <h2><a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h2>
                <p><span class="badge">{{ ucfirst(strtolower($course->publication_status)) }}</span></p>
                <p>{{ $course->summary }}</p>
                <p class="actions">
                    <a href="{{ route('admin.courses.edit', $course) }}">Edit</a>
                </p>
            </article>
            @endforeach
        </div>
        {{ $courses->links() }}
    </section>
@endsection
