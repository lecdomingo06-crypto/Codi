@extends('layouts.app')

@section('content')
    @php
        $languages = old('supported_languages', $latest->supported_languages ?? ['python', 'javascript']);
        $visibleTests = $latest?->testBundle?->testCases?->where('visibility', 'VISIBLE')->map(fn ($test) => ['name' => $test->name, 'input' => $test->input, 'expected_output' => $test->expected_output])->values()->all() ?? [['name' => 'sample 1', 'input' => '{"a":2,"b":3}', 'expected_output' => '5']];
        $hiddenTests = $latest?->testBundle?->testCases?->where('visibility', 'HIDDEN')->map(fn ($test) => ['name' => $test->name, 'input' => $test->input, 'expected_output' => $test->expected_output])->values()->all() ?? [['name' => 'hidden 1', 'input' => '{"a":10,"b":-4}', 'expected_output' => '6']];
        $conceptTags = $exercise->exists ? $exercise->concepts()->pluck('name')->join(', ') : 'functions, arithmetic';
    @endphp

    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>{{ $exercise->exists ? 'Edit exercise' : 'New exercise' }}</h1>
        @if($latest)
            <p>Latest version: v{{ $latest->version_number }} &middot; {{ $latest->status }}</p>
        @endif
        <form method="POST" action="{{ $exercise->exists ? route('admin.exercises.update', $exercise) : route('admin.exercises.store') }}" class="panel stack">
            @csrf
            @if($exercise->exists)
                @method('PATCH')
            @endif
            <label>Course
                <select name="course_id">
                    <option value="">None</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id', $exercise->course_id) === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>Lesson
                <select name="lesson_id">
                    <option value="">None</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected((string) old('lesson_id', $exercise->lesson_id) === (string) $lesson->id)>{{ $lesson->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>Title <input name="title" value="{{ old('title', $latest->title ?? $exercise->title) }}" required></label>
            <label>Slug <input name="slug" value="{{ old('slug', $exercise->slug) }}"></label>
            <label>Summary <input name="summary" value="{{ old('summary', $latest->summary ?? $exercise->summary) }}" required></label>
            <label>Difficulty
                <select name="difficulty" required>
                    @foreach(\App\Models\Exercise::DIFFICULTIES as $difficulty)
                        <option value="{{ $difficulty }}" @selected(old('difficulty', $latest->difficulty ?? $exercise->difficulty ?? 'EASY') === $difficulty)>{{ $difficulty }}</option>
                    @endforeach
                </select>
            </label>
            <label>Concept tags <input name="concept_tags" value="{{ old('concept_tags', $conceptTags) }}" required></label>
            <label>Description <textarea name="description_markdown" rows="8" required>{{ old('description_markdown', $latest->description_markdown ?? '') }}</textarea></label>
            <label>Constraints <textarea name="constraints" rows="4">{{ old('constraints', implode("\n", $latest->constraints ?? [])) }}</textarea></label>

            <fieldset>
                <legend>Supported languages</legend>
                @foreach(['python', 'javascript', 'typescript'] as $language)
                    <label class="inline">
                        <input type="checkbox" name="supported_languages[]" value="{{ $language }}" @checked(in_array($language, $languages, true))>
                        {{ $language }}
                    </label>
                @endforeach
            </fieldset>

            @foreach(['python', 'javascript', 'typescript'] as $language)
                <label>Starter code ({{ $language }})
                    <textarea name="starter_code_{{ $language }}" rows="6">{{ old('starter_code_'.$language, $latest->starter_code_by_language[$language] ?? '') }}</textarea>
                </label>
                <label>Function signature ({{ $language }})
                    <input name="function_signature_{{ $language }}" value="{{ old('function_signature_'.$language, $latest->function_signature_by_language[$language] ?? '') }}">
                </label>
                <label>Official solution ({{ $language }})
                    <textarea name="official_solution_{{ $language }}" rows="6">{{ old('official_solution_'.$language, $latest->official_solutions_by_language[$language] ?? '') }}</textarea>
                </label>
            @endforeach

            <label>Visible examples JSON <textarea name="visible_examples_json" rows="6">{{ old('visible_examples_json', json_encode($latest->visible_examples ?? [], JSON_PRETTY_PRINT)) }}</textarea></label>
            <label>Visible tests JSON <textarea name="visible_tests_json" rows="6" required>{{ old('visible_tests_json', json_encode($visibleTests, JSON_PRETTY_PRINT)) }}</textarea></label>
            <label>Hidden tests JSON <textarea name="hidden_tests_json" rows="6" required>{{ old('hidden_tests_json', json_encode($hiddenTests, JSON_PRETTY_PRINT)) }}</textarea></label>
            <label>Hints, one per line <textarea name="hints_text" rows="4">{{ old('hints_text', collect($latest->hints ?? [])->pluck('content_markdown')->join("\n")) }}</textarea></label>
            <label>Explanation <textarea name="explanation_markdown" rows="6">{{ old('explanation_markdown', $latest->explanation_markdown ?? '') }}</textarea></label>
            <label>Time limit ms <input name="time_limit_ms" type="number" min="100" value="{{ old('time_limit_ms', $latest->time_limit_ms ?? 2000) }}" required></label>
            <label>Memory limit MB <input name="memory_limit_mb" type="number" min="16" value="{{ old('memory_limit_mb', $latest->memory_limit_mb ?? 128) }}" required></label>
            <label>Points <input name="points" type="number" min="1" value="{{ old('points', $latest->points ?? 10) }}" required></label>
            <button type="submit">Save draft version</button>
        </form>

        @if($exercise->exists && $latest)
            <p><a href="{{ route('admin.exercises.preview', [$exercise, $latest]) }}">Preview latest version</a></p>
        @endif
    </section>
@endsection
