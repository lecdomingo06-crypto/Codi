@extends('layouts.app')

@section('content')
    <section class="stack">
        <h1>{{ $submission->exercise->title }} submission</h1>
        <p>{{ $submission->verdict }} &middot; {{ $submission->language }} &middot; {{ $submission->execution_time_ms }}ms &middot; {{ $submission->memory_kb }}KB</p>
        <section class="panel">
            <h2>Test results</h2>
            @foreach($submission->testResults as $result)
                <p>
                    {{ $result->test_name }}
                    &middot; {{ $result->visibility }}
                    &middot; {{ $result->verdict }}
                    &middot; {{ $result->message }}
                </p>
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
