@extends('layouts.app')

@section('content')
    <section class="narrow stack">
        <h1>Register</h1>
        <form method="POST" action="{{ route('register') }}" class="stack" data-register-form>
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
            <label>Password <input name="password" type="password" required data-password-input></label>
            <div class="password-strength" aria-live="polite" data-password-strength>
                <div class="password-strength__bar" data-password-strength-bar>
                    <span data-password-strength-label></span>
                </div>
            </div>
            <p class="field-message" data-password-message></p>
            <label>Confirm password <input name="password_confirmation" type="password" required data-password-confirmation></label>
            <p class="field-message" data-password-confirmation-message></p>
            <button type="submit" data-register-submit>Create account</button>
        </form>
    </section>
@endsection
