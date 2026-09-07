@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="hero-copy">
            <p class="eyebrow">Laravel learning platform</p>
            <h1>Learn a concept, practice it, then solve with confidence.</h1>
            <p class="lead">A focused MVP for lessons, guided exercises, challenge submissions, required difficulty, streaks, and progress tracking.</p>
            <p class="actions">
                @auth
                    <a class="button" href="{{ route('dashboard') }}">Open dashboard</a>
                @else
                    <a class="button" href="{{ route('register') }}">Create learner account</a>
                    <a class="button secondary" href="{{ route('login') }}">Sign in</a>
                @endauth
            </p>
        </div>

        <aside class="hero-visual" aria-label="Coding exercise preview">
            <header>
                <span class="dot red"></span>
                <span class="dot yellow"></span>
                <span class="dot green"></span>
            </header>
            <pre class="code-demo"><code><span class="comment"># Writing Small Functions</span>
def add(a, b):
    return a + b

visible_tests = [
    {"a": 2, "b": 3, "expected": 5},
    {"a": 0, "b": 8, "expected": 8},
]

verdict = <span class="good">"ACCEPTED"</span>
streak = "1 day"</code></pre>
        </aside>
    </section>

    <section class="catalog-grid">
        <article class="panel stack">
            <h2>Learn</h2>
            <p>Short lessons connect each exercise to concrete concepts and prerequisites.</p>
        </article>
        <article class="panel stack">
            <h2>Practice</h2>
            <p>Visible tests and progressive hints help learners close the gap before submitting.</p>
        </article>
        <article class="panel stack">
            <h2>Challenge</h2>
            <p>Hidden tests, immutable exercise versions, and submission history make solving durable.</p>
        </article>
    </section>

    <section class="panel stack">
        <h2>Seeded demo flow</h2>
        <p>Admin: <strong>admin@example.com</strong> / <strong>password</strong></p>
        <p>User: <strong>user@example.com</strong> / <strong>password</strong></p>
        <p class="actions">
            @auth
                <a class="button secondary" href="{{ route('catalog.index') }}">Browse catalog</a>
            @else
                <a class="button secondary" href="{{ route('login') }}">Try seeded accounts</a>
            @endauth
        </p>
    </section>
@endsection
