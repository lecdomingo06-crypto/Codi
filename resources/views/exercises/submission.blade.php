@extends('layouts.app')

@section('content')
    <section class="stack submission-page">
        <header class="page-heading">
            <div><p class="eyebrow">Submission details</p><h1>{{ $submission->exercise->title }}</h1><p>{{ $submission->submitted_at?->format('M j, Y · H:i') ?? $submission->created_at->format('M j, Y · H:i') }}</p></div>
            <x-verdict-badge :verdict="$submission->verdict" class="badge--large" />
        </header>
        <div class="submission-stats">
            <div><span>Language</span><strong>{{ $submission->language }}</strong></div>
            <div><span>Runtime</span><strong>{{ $submission->execution_time_ms ?? '—' }} ms</strong></div>
            <div><span>Memory</span><strong>{{ $submission->memory_kb ?? '—' }} KB</strong></div>
        </div>
        <section class="panel stack code-panel">
            <div class="section-heading"><h2>Submitted code</h2><span class="code-meta">{{ $submission->language }}</span></div>
            <pre><code>{{ $submission->source_code }}</code></pre>
        </section>
        <section class="panel stack">
            <h2>Test results</h2>
            @foreach($submission->testResults as $result)
                <div class="test-result-line"><span><strong>{{ $result->test_name }}</strong><small>{{ $result->visibility }}</small></span><x-verdict-badge :verdict="$result->verdict" /><span>{{ $result->message }}</span></div>
            @endforeach
        </section>
        @if($submission->verdict === 'ACCEPTED' && $submission->exerciseVersion->explanation_markdown)
            <section class="panel">
                <h2>Explanation</h2>
                <div>{!! nl2br(e($submission->exerciseVersion->explanation_markdown)) !!}</div>
            </section>
        @endif
    </section>
@endsection
