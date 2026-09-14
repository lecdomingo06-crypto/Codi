@extends('layouts.app')

@php
    $avatarInitial = strtoupper(substr($user->name, 0, 1));
    $avatarUrl = $user->avatarUrl();
    $anonymousUsername = Str::studly(Str::before($user->email, '@')).$user->id;
@endphp

@section('content')
    <section class="profile-edit-shell">
        <a class="profile-edit-back" href="{{ route('profile.show') }}">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            Back to Profile
        </a>

        <div class="profile-edit-grid">
            <section class="profile-edit-column">
                <h1>Profile</h1>

                <form class="profile-edit-card" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="profile-form-section">
                        <h2>Profile Photo</h2>
                        <p>Upload a custom profile photo or keep your current avatar.</p>
                        <div class="profile-photo-control">
                            <span class="profile-photo-preview" aria-hidden="true">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="">
                                @else
                                    {{ $avatarInitial }}
                                @endif
                            </span>
                            <label class="profile-upload-button">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h4l2-2h4l2 2h4v12H4z"/><circle cx="12" cy="13" r="3"/></svg>
                                Upload Photo
                                <input type="file" name="avatar" accept="image/*">
                            </label>
                        </div>
                        <small>Max 2MB. JPG, PNG, GIF, or WebP recommended.</small>
                    </div>

                    <div class="profile-form-section">
                        <h2>Display Name</h2>
                        <p>This is the name shown on your profile and community posts.</p>
                        <label class="sr-only" for="profile-name">Display name</label>
                        <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required>
                        <small>Maximum 255 characters.</small>
                    </div>

                    <div class="profile-form-section">
                        <h2>Timezone</h2>
                        <p>This controls daily streak timing and contribution dates.</p>
                        <label class="sr-only" for="profile-timezone">Timezone</label>
                        <select id="profile-timezone" name="timezone" required>
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $user->timezone) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="profile-form-section">
                        <h2>Anonymous Username</h2>
                        <p>This is your permanent anonymous username for anonymous posts and versus play.</p>
                        <span class="profile-readonly">{{ $anonymousUsername }}</span>
                        <small>This username cannot be changed.</small>
                    </div>

                    <button class="button button--success" type="submit">Save profile</button>
                </form>
            </section>

            <section class="profile-edit-column">
                <h1>Account</h1>

                <section class="profile-edit-card profile-password-card">
                    <header>
                        <h2>Change password</h2>
                        <p>Use a strong password that you do not use anywhere else.</p>
                    </header>
                    <form method="POST" action="{{ route('profile.password.update') }}" class="profile-password-form">
                        @csrf
                        @method('PATCH')
                        <label>Current password
                            <span class="password-field">
                                <input name="current_password" type="password" autocomplete="current-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button>
                            </span>
                        </label>
                        <label>New password
                            <span class="password-field">
                                <input name="password" type="password" autocomplete="new-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button>
                            </span>
                        </label>
                        <label>Confirm new password
                            <span class="password-field">
                                <input name="password_confirmation" type="password" autocomplete="new-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">Show</button>
                            </span>
                        </label>
                        <button class="button button--primary" type="submit">Update password</button>
                    </form>
                </section>

                <section class="profile-edit-card profile-integration-card">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.9a3.4 3.4 0 0 0-.9-2.6c3-.3 6.1-1.5 6.1-6.7a5.2 5.2 0 0 0-1.4-3.6 4.8 4.8 0 0 0-.1-3.6s-1.1-.3-3.7 1.4a12.8 12.8 0 0 0-6.7 0C6.7.3 5.6.6 5.6.6a4.8 4.8 0 0 0-.1 3.6 5.2 5.2 0 0 0-1.4 3.6c0 5.2 3.1 6.4 6.1 6.7a3.4 3.4 0 0 0-.9 2.6V22"/></svg>
                    <h2>GitHub Integration</h2>
                    <p>Automatically sync accepted solutions to a GitHub repository you own.</p>
                    <button class="button button--secondary" type="button">Connect GitHub Account</button>
                </section>
            </section>
        </div>
    </section>
@endsection
