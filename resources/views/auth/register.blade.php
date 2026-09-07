@extends('layouts.app')

@section('content')
    <section class="auth-shell">
        <div class="auth-card stack narrow">
            <a class="auth-brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">&lt;/&gt;</span> Coddy</a>
            <div><p class="eyebrow">Start your practice</p><h1>Create your account.</h1><p>Set up your workspace and start solving your first problem.</p></div>
        <form method="POST" action="{{ route('register') }}" class="stack">
            @csrf
            <label>Name <input name="name" value="{{ old('name') }}" autocomplete="name" required autofocus></label>
            <label>Email address <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
            <label>Timezone
                <select name="timezone" required>
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', 'Asia/Manila') === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </label>
            <label>Password <span class="password-field"><input name="password" type="password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button></span></label>
            <label>Confirm password <span class="password-field"><input name="password_confirmation" type="password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button></span></label>
            <button type="submit" class="button--full">Create account <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </section>
@endsection
