@extends('layouts.app')

@section('content')
    <section class="leetcode-workspace">
        <div class="workspace-topbar">
            <div>
                <p class="eyebrow">Challenge workspace</p>
                <h1>{{ $version->title }} <span class="badge">{{ $version->difficulty }}</span></h1>
            </div>
            <div class="workspace-actions">
                <a href="{{ route('catalog.index') }}">Problem list</a>
            </div>
        </div>

        <div class="workspace-grid">
            <aside class="problem-panel">
                <div class="workspace-tabs" aria-label="Exercise sections">
                    <span class="active">Description</span>
                    <span>Hints</span>
                    <span>Submissions</span>
                </div>

                <div class="problem-scroll stack">
                    <p class="lead">{{ $version->summary }}</p>
                    <p>Concepts: {{ $exercise->concepts->pluck('name')->join(', ') }}</p>
                    <div>{!! nl2br(e($version->description_markdown)) !!}</div>

                    @if($version->constraints)
                        <h2>Constraints</h2>
                        <ul>
                            @foreach($version->constraints as $constraint)
                                <li>{{ $constraint }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <h2>Visible Tests</h2>
                    @foreach($visibleTests as $test)
                        <div class="case-card">
                            <strong>{{ $test->name }}</strong>
                            <span>Expected: {{ $test->expected_output }}</span>
                        </div>
                    @endforeach

                    @if($version->hints)
                        <h2>Hints</h2>
                        @foreach($version->hints as $hint)
                            <details>
                                <summary>Hint {{ $hint['order'] }}</summary>
                                <p>{{ $hint['content_markdown'] }}</p>
                            </details>
                        @endforeach
                    @endif
                </div>
            </aside>

            <section class="editor-panel">
                <div class="editor-toolbar">
                    <strong>&lt;/&gt; Code</strong>
                    <label>Language
                        <select name="language" form="run-form" data-editor-language>
                            @foreach($version->supported_languages as $language)
                                <option value="{{ $language }}" @selected(old('language', $version->supported_languages[0] ?? 'python') === $language)>{{ $language }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <form
                    id="run-form"
                    method="POST"
                    action="{{ route('exercises.run', $exercise) }}"
                    class="editor-form"
                    data-auto-check
                    data-check-url="{{ route('exercises.check', $exercise) }}"
                >
                    @csrf
                    <label class="sr-only">Code</label>
                    <textarea
                        name="source_code"
                        rows="18"
                        spellcheck="false"
                        data-code-editor
                    >{{ old('source_code', $starterCode) }}</textarea>
                </form>

                <div class="result-dock">
                    <div class="result-tabs">
                        <strong>Testcase</strong>
                        <strong>Test Result</strong>
                        <span data-auto-check-status>Auto-check waits for your edits.</span>
                    </div>
                    <div class="result-list" data-auto-check-results>
                        @if($lastRun)
                            <h2>Last run: {{ $lastRun->verdict }}</h2>
                            @foreach($lastRun->testResults as $result)
                                <p>{{ $result->test_name }} &middot; {{ $result->verdict }} &middot; {{ $result->message }}</p>
                            @endforeach
                        @else
                            <p>Visible test feedback will appear here as you type.</p>
                        @endif
                    </div>
                </div>

                <div class="editor-actions">
                    <button type="button" data-run-button>Run</button>
                    <form method="POST" action="{{ route('exercises.submit', $exercise) }}" data-submit-from-editor>
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                        <input type="hidden" name="language" value="{{ $version->supported_languages[0] ?? 'python' }}" data-submit-language>
                        <input type="hidden" name="source_code" value="{{ old('source_code', $starterCode) }}" data-submit-code>
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </section>
        </div>

        <section class="panel stack workspace-history">
            <h2>Previous submissions</h2>
            @forelse($submissions as $submission)
                <p><a href="{{ route('submissions.show', $submission) }}">{{ $submission->submitted_at->format('Y-m-d H:i') }}</a> &middot; {{ $submission->verdict }}</p>
            @empty
                <p>No submissions yet.</p>
            @endforelse
        </section>
    </section>
@endsection
