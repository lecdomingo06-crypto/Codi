@extends('layouts.app')

@section('content')
    <section class="stack progress-page">
        <header class="page-heading"><div><p class="eyebrow">Your progress</p><h1>Stay in the flow.</h1><p>Every attempt compounds into stronger problem-solving instincts.</p></div></header>
        <section class="stats-section" aria-label="Progress summary">
            <div class="section-heading"><h2>Practice summary</h2><span class="count-label">Last 12 months</span></div>
            <div class="stats-list">
                <div><span>Current streak</span><strong>{{ $user->streak?->current_streak ?? 0 }} days</strong></div>
                <div><span>Longest streak</span><strong>{{ $user->streak?->longest_streak ?? 0 }} days</strong></div>
                <div><span>Attempts</span><strong>{{ $calendar['total_answers'] }}</strong></div>
                <div><span>Accepted</span><strong>{{ $calendar['total_accepted'] }}</strong></div>
            </div>
        </section>

        @include('shared.activity-calendar', ['calendar' => $calendar])

        <section class="content-section stack">
            <div class="section-heading"><div><p class="eyebrow">Skills</p><h2>Concept mastery</h2></div></div>
            @forelse($masteries as $mastery)
                <div class="mastery-row"><span>{{ $mastery->name }}</span><span class="mastery-bar" aria-label="{{ $mastery->mastery_score }} mastery"><i style="width: {{ min(100, $mastery->mastery_score) }}%"></i></span><strong>{{ $mastery->mastery_score }}</strong></div>
            @empty
                <div class="empty-state"><span aria-hidden="true">⌁</span><p>Complete exercises to begin tracking concept mastery.</p></div>
            @endforelse
        </section>
    </section>
@endsection
