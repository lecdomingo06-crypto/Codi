@extends('layouts.app')

@section('content')
    <section class="stack">
        <h1>Progress</h1>
        <div class="metrics">
            <p><strong>{{ $user->streak?->current_streak ?? 0 }}</strong><span>current streak</span></p>
            <p><strong>{{ $user->streak?->longest_streak ?? 0 }}</strong><span>longest streak</span></p>
            <p><strong>{{ $calendar['total_answers'] }}</strong><span>answers last year</span></p>
            <p><strong>{{ $calendar['total_accepted'] }}</strong><span>accepted last year</span></p>
        </div>

        @include('shared.activity-calendar', ['calendar' => $calendar])

        <section class="panel">
            <h2>Concept mastery</h2>
            @forelse($masteries as $mastery)
                <p>{{ $mastery->name }} &middot; {{ $mastery->mastery_score }}</p>
            @empty
                <p>No mastery records yet.</p>
            @endforelse
        </section>
    </section>
@endsection
