@extends('layouts.app')

@section('content')
    <section class="leetcode-workspace">
        <div class="workspace-topbar">
            <div>
                <p class="workspace-breadcrumb"><a href="{{ route('catalog.index') }}">Problems</a><span aria-hidden="true">/</span>Challenge workspace</p>
                <h1>{{ $version->title }} <x-difficulty-badge :difficulty="$version->difficulty" /></h1>
            </div>
            <div class="workspace-actions">
                <span class="workspace-limit">{{ $version->time_limit_ms }} ms <span aria-hidden="true">·</span> {{ $version->memory_limit_mb }} MB</span>
                <a class="button button--secondary button--small" href="{{ route('catalog.index') }}">Problem list</a>
            </div>
        </div>

        <div class="workspace-grid">
            <aside class="problem-panel">
                <div class="workspace-tabs" role="tablist" aria-label="Exercise sections">
                    <button class="active" type="button" role="tab" aria-selected="true" aria-controls="description-panel" id="description-tab" data-workspace-tab="description-panel">Description</button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="hints-panel" id="hints-tab" data-workspace-tab="hints-panel">Hints</button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="submissions-panel" id="submissions-tab" data-workspace-tab="submissions-panel">Submissions <span class="tab-count">{{ $submissions->count() }}</span></button>
                </div>

                <div class="problem-scroll stack workspace-tab-panel" id="description-panel" role="tabpanel" aria-labelledby="description-tab">
                    <p class="lead">{{ $version->summary }}</p>
                    @if($exercise->concepts->isNotEmpty())
                        <p class="topic-pills"><span>Topics</span>@foreach($exercise->concepts as $concept)<span class="topic-pill">{{ $concept->name }}</span>@endforeach</p>
                    @endif
                    <div>{!! nl2br(e($version->description_markdown)) !!}</div>

                    @if($version->constraints)
                        <h2>Constraints</h2>
                        <ul>
                            @foreach($version->constraints as $constraint)
                                <li>{{ $constraint }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <h2>Examples</h2>
                    @foreach($visibleTests as $test)
                        <div class="case-card stack">
                            <strong>Example {{ $loop->iteration }}{{ $test->name ? ': '.$test->name : '' }}</strong>
                            @if($test->input)<span><b>Input</b>{{ $test->input }}</span>@endif
                            <span><b>Output</b>{{ $test->expected_output }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="problem-scroll stack workspace-tab-panel" id="hints-panel" role="tabpanel" aria-labelledby="hints-tab" hidden>
                    <div><p class="eyebrow">Need a nudge?</p><h2>Hints</h2><p class="workspace-help">Hints reveal the next useful idea without giving away the complete solution.</p></div>
                    @if($version->hints)
                        @foreach($version->hints as $hint)
                            <details>
                                <summary>Hint {{ $hint['order'] }}</summary>
                                <p>{{ $hint['content_markdown'] }}</p>
                            </details>
                        @endforeach
                    @else
                        <div class="empty-state"><span aria-hidden="true">⌁</span><p>No hints have been added for this problem.</p></div>
                    @endif
                </div>

                <div class="problem-scroll stack workspace-tab-panel" id="submissions-panel" role="tabpanel" aria-labelledby="submissions-tab" hidden>
                    <div><p class="eyebrow">Your attempts</p><h2>Previous submissions</h2></div>
                    @forelse($submissions as $submission)
                        <a class="workspace-submission" href="{{ route('submissions.show', $submission) }}"><span><x-verdict-badge :verdict="$submission->verdict" /><small>{{ $submission->submitted_at->format('M j, Y · H:i') }}</small></span><span class="code-meta">{{ $submission->language }} · {{ $submission->execution_time_ms ?? '—' }}ms</span></a>
                    @empty
                        <div class="empty-state"><span aria-hidden="true">⌘</span><p>Submit a solution to start building your history.</p></div>
                    @endforelse
                </div>
            </aside>

            <section class="editor-panel">
                <div class="editor-toolbar">
                    <strong>&lt;/&gt; Editor</strong>
                    <label>Language
                        <select name="language" form="run-form" data-editor-language aria-label="Programming language">
                            @php($languageLabels = ['python' => 'Python', 'javascript' => 'JavaScript', 'typescript' => 'TypeScript', 'php' => 'PHP', 'cpp' => 'C++'])
                            @foreach($version->supported_languages as $language)
                                <option value="{{ $language }}" @selected(old('language', $version->supported_languages[0] ?? 'python') === $language)>{{ $languageLabels[$language] ?? $language }}</option>
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
                        data-starter-codes='@json($version->starter_code_by_language)'
                    >{{ old('source_code', $starterCode) }}</textarea>
                </form>

                <div class="result-dock">
                    <div class="result-tabs" role="tablist" aria-label="Test panel">
                        <button class="active" type="button" role="tab" aria-selected="true" data-result-tab="result-output">Test result</button>
                        <button type="button" role="tab" aria-selected="false" data-result-tab="testcase-output">Testcase</button>
                        <span data-auto-check-status>Auto-check waits for your edits.</span>
                    </div>
                    <div id="result-output" class="result-list" role="tabpanel" data-auto-check-results>
                        @if($lastRun)
                            <div class="verdict {{ $lastRun->verdict === 'ACCEPTED' ? 'accepted' : 'failed' }}">{{ str_replace('_', ' ', $lastRun->verdict) }}</div>
                            @foreach($lastRun->testResults as $result)
                                <div class="result-row {{ $result->verdict === 'ACCEPTED' ? 'accepted' : 'failed' }}"><strong>{{ $result->test_name }}</strong><span>{{ $result->verdict }}</span><small>{{ $result->message }}</small></div>
                            @endforeach
                        @else
                            <div class="result-placeholder"><span aria-hidden="true">▷</span><p>Run your code to see visible test results here.</p></div>
                        @endif
                    </div>
                    <div id="testcase-output" class="result-list testcase-list" role="tabpanel" hidden><p>Visible examples are used when you run your code.</p>@forelse($visibleTests as $test)<div class="testcase-row"><strong>{{ $test->name ?: 'Example '.$loop->iteration }}</strong><code>{{ $test->input }}</code></div>@empty<p>No visible test cases are available.</p>@endforelse</div>
                </div>

                <div class="editor-actions">
                    <button type="button" class="button--secondary" data-run-button>Run code</button>
                    <form method="POST" action="{{ route('exercises.submit', $exercise) }}" data-submit-from-editor>
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                        <input type="hidden" name="language" value="{{ $version->supported_languages[0] ?? 'python' }}" data-submit-language>
                        <input type="hidden" name="source_code" value="{{ old('source_code', $starterCode) }}" data-submit-code>
                        <button type="submit">Submit solution</button>
                    </form>
                </div>
            </section>
        </div>
    </section>
@endsection
