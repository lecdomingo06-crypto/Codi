@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · version preview</p><h1>{{ $version->title }}</h1><p><x-difficulty-badge :difficulty="$version->difficulty" /> <span class="badge">Version {{ $version->version_number }}</span> <span class="badge">{{ ucfirst(strtolower($version->status)) }}</span></p></div></header>
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
