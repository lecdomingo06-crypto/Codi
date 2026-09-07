@extends('layouts.app')

@section('content')
    <section class="profile-page stack">
        <header class="profile-header panel">
            <span class="profile-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div><p class="eyebrow">Developer profile</p><h1>{{ auth()->user()->name }}</h1><p>{{ auth()->user()->email }} <span aria-hidden="true">·</span> {{ strtolower(auth()->user()->role) }} account</p></div>
            <div class="profile-points"><strong>{{ auth()->user()->points }}</strong><span>points earned</span></div>
        </header>
        <section class="narrow stack profile-form">
        <div><p class="eyebrow">Settings</p><h2>Account details</h2><p>Keep your profile and local practice time up to date.</p></div>
        <form method="POST" action="{{ route('profile.update') }}" class="panel stack">
            @csrf
            @method('PATCH')
            <label>Name <input name="name" value="{{ old('name', auth()->user()->name) }}" required></label>
            <label>Timezone
                <select name="timezone" required>
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', auth()->user()->timezone) === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Save changes</button>
        </form>
        </section>
    </section>
@endsection
