@extends('layouts.app')

@section('content')
    <section class="stack">
        <p class="eyebrow">Admin</p>
        <h1>Import exercises</h1>
        <form method="POST" action="{{ route('admin.exercises.import') }}" class="panel stack">
            @csrf
            <label>JSON payload
                <textarea name="payload" rows="18" required>{{ old('payload', '[{"title":"Array Sum","slug":"array-sum","summary":"Return the sum of an array.","description_markdown":"Write a function that returns the sum of the given integers.","difficulty":"EASY","concepts":["arrays","loops"],"supported_languages":["python","javascript"],"visible_tests":[{"name":"sample","input":"[1,2,3]","expected_output":"6"}],"hidden_tests":[{"name":"hidden","input":"[-1,1,5]","expected_output":"5"}]}]') }}</textarea>
            </label>
            <button type="submit">Import drafts</button>
        </form>
    </section>
@endsection
