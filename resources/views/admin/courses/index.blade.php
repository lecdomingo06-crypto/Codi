@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>Courses</h1>
        <p class="actions"><a class="button" href="{{ route('admin.courses.create') }}">New course</a></p>
        <div class="catalog-grid">
            @foreach($courses as $course)
            <article class="panel stack">
                <h2>{{ $course->title }} &middot; {{ $course->publication_status }}</h2>
                <p>{{ $course->summary }}</p>
                <p><a href="{{ route('admin.courses.edit', $course) }}">Edit</a></p>
            </article>
            @endforeach
        </div>
        {{ $courses->links() }}
    </section>
@endsection
