@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · course structure</p><h1>Modules</h1><p>Arrange lessons into clear sections within each course.</p></div></header>
        <p class="actions"><a class="button" href="{{ route('admin.modules.create', request()->only('course_id')) }}">New module</a></p>
        <div class="catalog-grid">
            @foreach($modules as $module)
                <article class="panel stack">
                    <h2>{{ $module->title }}</h2>
                    <p>{{ $module->course?->title }} · Order {{ $module->sort_order }}</p>
                    <p>{{ $module->summary ?: 'No summary yet.' }}</p>
                    <p><a href="{{ route('admin.modules.edit', $module) }}">Edit</a></p>
                </article>
            @endforeach
        </div>
        {{ $modules->links() }}
    </section>
@endsection
