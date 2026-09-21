@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · accounts</p><h1>User management</h1></div></header>
        <section class="panel empty-state"><span aria-hidden="true">⌁</span><p>Account management is unavailable.</p><a href="{{ route('admin.dashboard') }}">Back to admin dashboard</a></section>
    </section>
@endsection
