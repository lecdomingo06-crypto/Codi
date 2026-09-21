@extends('layouts.app')

@section('content')
    <section class="stack admin-page">
        <header class="page-heading"><div><p class="eyebrow">Admin · insights</p><h1>Learning analytics</h1><p>View submission and content totals in the <a href="{{ route('admin.dashboard') }}">admin dashboard</a>.</p></div></header>
        <section class="panel empty-state"><span aria-hidden="true">⌁</span><p>Detailed reports are unavailable.</p></section>
    </section>
@endsection
