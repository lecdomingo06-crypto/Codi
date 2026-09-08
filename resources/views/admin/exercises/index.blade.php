@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · challenge library</p><h1>Exercises</h1><p>Create, review, and publish coding challenges.</p></div></header>
        <p class="actions">
            <a class="button" href="{{ route('admin.exercises.create') }}">New exercise</a>
            <a class="button secondary" href="{{ route('admin.exercises.import.form') }}">Import JSON</a>
        </p>
        <div class="catalog-grid">
            @foreach($exercises as $exercise)
            <article class="panel stack">
                <h2>{{ $exercise->title }} <x-difficulty-badge :difficulty="$exercise->difficulty" /></h2>
                <p><span class="badge">{{ ucfirst(strtolower($exercise->publication_status)) }}</span></p>
                <p>{{ $exercise->summary }}</p>
                <p>Concepts: {{ $exercise->concepts->pluck('name')->join(', ') }}</p>
                <p class="actions">
                    <a href="{{ route('admin.exercises.edit', $exercise) }}">Edit</a>
                    @if($exercise->latestVersion)
                        <a href="{{ route('admin.exercises.preview', [$exercise, $exercise->latestVersion]) }}">Preview latest</a>
                    @endif
                </p>
                @if($exercise->latestVersion && $exercise->latestVersion->status !== 'PUBLISHED')
                    <form method="POST" action="{{ route('admin.exercises.publish', [$exercise, $exercise->latestVersion]) }}">
                        @csrf
                        <button type="submit">Publish latest draft</button>
                    </form>
                @endif
                @if($exercise->publication_status === 'PUBLISHED')
                    <form method="POST" action="{{ route('admin.exercises.archive', $exercise) }}">
                        @csrf
                        <button type="submit">Archive</button>
                    </form>
                @endif
            </article>
            @endforeach
        </div>
        {{ $exercises->links() }}
    </section>
@endsection
