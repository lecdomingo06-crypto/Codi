@extends('layouts.app')

@section('content')
    <section class="narrow stack">
        <h1>Profile</h1>
        <form method="POST" action="{{ route('profile.update') }}" class="stack">
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
            <button type="submit">Save profile</button>
        </form>
    </section>
@endsection
