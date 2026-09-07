<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Coddy' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('app.css') }}?v={{ filemtime(public_path('app.css')) }}">
    <script defer src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}"></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">C</span>
            <span>Coddy</span>
        </a>
        <nav aria-label="Primary navigation">
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('catalog.index') }}">Catalog</a>
                <a href="{{ route('progress.show') }}">Progress</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <a href="{{ route('profile.edit') }}">Profile</a>
                <span class="user-chip">{{ auth()->user()->role }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
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
