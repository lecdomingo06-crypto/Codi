@extends('layouts.app')

@section('content')
    <section class="stack">
        <h1>Preview: {{ $version->title }}</h1>
        <p>{{ $version->difficulty }} &middot; version {{ $version->version_number }} &middot; {{ $version->status }}</p>
        <section class="panel">
            <h2>Description</h2>
            <div>{!! nl2br(e($version->description_markdown)) !!}</div>
        </section>
        <section class="panel">
            <h2>Tests</h2>
            <p>Visible: {{ $version->testBundle?->testCases->where('visibility', 'VISIBLE')->count() ?? 0 }}</p>
            <p>Hidden: {{ $version->testBundle?->testCases->where('visibility', 'HIDDEN')->count() ?? 0 }}</p>
        </section>
        @if($version->status !== 'PUBLISHED')
            <form method="POST" action="{{ route('admin.exercises.publish', [$exercise, $version]) }}">
                @csrf
                <button type="submit">Publish this version</button>
            </form>
        @endif
    </section>
@endsection
