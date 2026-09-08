@extends('layouts.app')

@section('content')
    <section class="auth-shell">
        <div class="auth-card stack narrow">
            <a class="auth-brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">&lt;/&gt;</span> Coddy</a>
            <div><p class="eyebrow">Welcome back</p><h1>Sign in to continue.</h1><p>Pick up where you left off and keep building your streak.</p></div>
        <form method="POST" action="{{ route('login') }}" class="stack">
            @csrf
            <label>Email address <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
            <label>Password <span class="password-field"><input name="password" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button></span></label>
            <label class="inline"><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button type="submit" class="button--full">Sign in <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-footer">New to Coddy? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </section>
@endsection
