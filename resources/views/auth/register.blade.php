@extends('layouts.app')

@section('content')
    <section class="narrow stack">
        <h1>Register</h1>
        <form method="POST" action="{{ route('register') }}" class="stack">
            @csrf
            <label>Name <input name="name" value="{{ old('name') }}" required autofocus></label>
            <label>Email <input name="email" type="email" value="{{ old('email') }}" required></label>
            <label>Timezone
                <select name="timezone" required>
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', 'Asia/Manila') === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </label>
            <label>Password <input name="password" type="password" required></label>
            <label>Confirm password <input name="password_confirmation" type="password" required></label>
            <button type="submit">Create account</button>
        </form>
    </section>
@endsection
