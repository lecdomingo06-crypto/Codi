@extends('layouts.app')

@section('content')
    <section class="narrow stack">
        <h1>Login</h1>
        <form method="POST" action="{{ route('login') }}" class="stack">
            @csrf
            <label>Email <input name="email" type="email" value="{{ old('email') }}" required autofocus></label>
            <label>Password <input name="password" type="password" required></label>
            <label class="inline"><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button type="submit">Sign in</button>
        </form>
    </section>
@endsection
