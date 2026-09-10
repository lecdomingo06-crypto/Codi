<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Coddy — Practice with purpose' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('app.css') }}?v={{ filemtime(public_path('app.css')) }}">
        <script defer src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}"></script>
    @endif
</head>
<body data-theme="dark">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}" aria-label="Coddy home">
            <span class="brand-mark" aria-hidden="true">&lt;/&gt;</span>
            <span class="brand-name">Coddy</span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open navigation" aria-expanded="false" aria-controls="primary-nav">
            <span></span><span></span><span></span>
        </button>

        <nav id="primary-nav" class="primary-nav" aria-label="Primary navigation">
            @auth
                <a class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="{{ request()->routeIs('catalog.*', 'lessons.*', 'exercises.*') ? 'is-active' : '' }}" href="{{ route('catalog.index') }}">Problems</a>
                <a class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}" href="{{ route('courses.index') }}">Courses</a>
                <a class="{{ request()->routeIs('progress.*', 'submissions.*') ? 'is-active' : '' }}" href="{{ route('progress.show') }}">Progress</a>
                @if(auth()->user()->isAdmin())
                    <a class="{{ request()->routeIs('admin.*') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <form class="nav-search" method="GET" action="{{ route('catalog.index') }}" role="search">
                    <label class="sr-only" for="nav-search">Search problems</label>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m21 21-4.35-4.35m2.35-5.15a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
                    <input id="nav-search" name="search" type="search" placeholder="Search problems">
                </form>
                <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch to light theme" title="Switch theme">
                    <svg class="theme-icon-moon" aria-hidden="true" viewBox="0 0 24 24"><path d="M20.7 15.1A8.5 8.5 0 0 1 8.9 3.3 8.5 8.5 0 1 0 20.7 15.1Z"/></svg>
                    <svg class="theme-icon-sun" aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                </button>
                <details class="account-menu">
                    <summary aria-label="Open account menu">
                        <span class="avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="account-name">{{ auth()->user()->name }}</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                    </summary>
                    <div class="account-dropdown">
                        <div class="account-dropdown__identity">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                        </div>
                        <a href="{{ route('profile.edit') }}">Profile &amp; settings</a>
                        <a href="{{ route('progress.show') }}">My progress</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="menu-action">Sign out</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}">Sign in</a>
                <a class="button button--small" href="{{ route('register') }}">Get started</a>
            @endauth
        </nav>
    </header>

    <main id="main" class="page">
        @if(session('status'))
            <p class="notice">{{ session('status') }}</p>
        @endif

        @if($errors->any())
            <section class="errors" aria-live="polite">
                <strong>Check these fields:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
