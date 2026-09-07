@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · learning content</p><h1>Lessons</h1><p>Write concise lessons that lead into practice.</p></div></header>
        <p class="actions"><a class="button" href="{{ route('admin.lessons.create') }}">New lesson</a></p>
        <div class="catalog-grid">
            @foreach($lessons as $lesson)
            <article class="panel stack">
                <h2>{{ $lesson->title }}</h2>
                <p><span class="badge">{{ ucfirst(strtolower($lesson->publication_status)) }}</span></p>
                <p>{{ $lesson->course?->title }}</p>
                <p><a href="{{ route('admin.lessons.edit', $lesson) }}">Edit</a></p>
            </article>
            @endforeach
        </div>
        {{ $lessons->links() }}
    </section>
@endsection
