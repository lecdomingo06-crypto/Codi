@extends('layouts.app')

@section('content')
    <section class="hero home-hero">
        <div class="hero-copy">
            <p class="eyebrow">Programming practice</p>
            <h1>Write better code, one problem at a time.</h1>
            <p class="lead">Learn the concept, test your thinking, and submit solutions in one focused workspace.</p>
            <p class="actions">
                @auth
                    <a class="button" href="{{ route('community.index') }}">Open community</a>
                @else
                    <a class="button" href="{{ route('register') }}">Start practicing <span aria-hidden="true">→</span></a>
                    <a class="button button--secondary" href="{{ route('login') }}">Sign in</a>
                @endauth
            </p>
            <p class="hero-note"><span aria-hidden="true">✓</span> Learn by doing, at your own pace.</p>
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

    <section class="feature-section stack">
        <div class="section-heading"><div><p class="eyebrow">How Coddy works</p><h2>A clear path from concept to solution.</h2></div></div>
        <div class="feature-grid">
        <article class="feature-card">
            <h3>Learn the why</h3>
            <p>Short lessons connect every exercise to a concrete concept and its prerequisites.</p>
        </article>
        <article class="feature-card">
            <h3>Practice deliberately</h3>
            <p>Visible tests and progressive hints help learners close the gap before submitting.</p>
        </article>
        <article class="feature-card">
            <h3>Prove your solution</h3>
            <p>Hidden tests, immutable exercise versions, and submission history make solving durable.</p>
        </article>
        </div>
    </section>

    <section class="content-section stack demo-panel">
        <div><p class="eyebrow">Demo access</p><h2>Explore the complete learning flow.</h2></div>
        <p class="demo-credentials"><span>Admin</span><code>admin@example.com</code><code>password</code><span>User</span><code>user@example.com</code><code>password</code></p>
        <p class="actions">
            @auth
                <a class="button button--secondary" href="{{ route('catalog.index') }}">Browse problems</a>
            @else
                <a class="button button--secondary" href="{{ route('login') }}">Try the demo</a>
            @endauth
        </p>
    </section>
@endsection
