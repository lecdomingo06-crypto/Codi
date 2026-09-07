@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>Lessons</h1>
        <p class="actions"><a class="button" href="{{ route('admin.lessons.create') }}">New lesson</a></p>
        <div class="catalog-grid">
            @foreach($lessons as $lesson)
            <article class="panel stack">
                <h2>{{ $lesson->title }} &middot; {{ $lesson->publication_status }}</h2>
                <p>{{ $lesson->course?->title }}</p>
                <p><a href="{{ route('admin.lessons.edit', $lesson) }}">Edit</a></p>
            </article>
            @endforeach
        </div>
        {{ $lessons->links() }}
    </section>
@endsection
