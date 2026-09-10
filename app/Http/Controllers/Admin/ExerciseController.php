<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Concept;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\ExerciseVersion;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.exercises.index', [
            'exercises' => Exercise::with('activeVersion', 'concepts')
                ->when($request->integer('course_id'), fn ($query, $courseId) => $query->where('course_id', $courseId))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Exercise());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);

        $exercise = DB::transaction(function () use ($request, $data): Exercise {
            $exercise = Exercise::create([
                'course_id' => $data['course_id'],
                'lesson_id' => $data['lesson_id'],
                'created_by' => $request->user()->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'summary' => $data['summary'],
                'difficulty' => $data['difficulty'],
                'publication_status' => 'DRAFT',
            ]);

            $this->storeDraftVersion($exercise, $data, $request->user()->id);

            return $exercise;
        });

        return redirect()->route('admin.exercises.edit', $exercise)->with('status', 'Draft exercise saved.');
    }

    public function edit(Exercise $exercise): View
    {
        return $this->form($exercise);
    }

    public function update(Request $request, Exercise $exercise): RedirectResponse
    {
        $data = $this->validatedPayload($request, $exercise);

        DB::transaction(function () use ($request, $exercise, $data): void {
            $exercise->update([
                'course_id' => $data['course_id'],
                'lesson_id' => $data['lesson_id'],
                'title' => $data['title'],
                'slug' => $data['slug'],
                'summary' => $data['summary'],
                'difficulty' => $data['difficulty'],
                'publication_status' => $exercise->active_version_id ? $exercise->publication_status : 'DRAFT',
            ]);

            $this->storeDraftVersion($exercise, $data, $request->user()->id);
        });

        return redirect()->route('admin.exercises.edit', $exercise)->with('status', 'New draft version saved.');
    }

    public function preview(Exercise $exercise, ExerciseVersion $version): View
    {
        abort_unless($version->exercise_id === $exercise->id, 404);

        return view('admin.exercises.preview', [
            'exercise' => $exercise,
            'version' => $version->load('testBundle.testCases'),
        ]);
    }

    public function publish(Request $request, Exercise $exercise, ExerciseVersion $version): RedirectResponse
    {
        abort_unless($version->exercise_id === $exercise->id, 404);
        $version->load('testBundle.testCases');

        $errors = [];
        if (! in_array($version->difficulty, Exercise::DIFFICULTIES, true)) {
            $errors['difficulty'] = 'Select EASY, MEDIUM, or HARD before publishing.';
        }

        if (! $version->testBundle || ! $version->testBundle->testCases->where('visibility', 'VISIBLE')->count()) {
            $errors['visible_tests_json'] = 'At least one visible test is required.';
        }

        if (! $version->testBundle || ! $version->testBundle->testCases->where('visibility', 'HIDDEN')->count()) {
            $errors['hidden_tests_json'] = 'At least one hidden test is required.';
        }

        if ($errors) {
            return back()->withErrors($errors);
        }

        DB::transaction(function () use ($request, $exercise, $version): void {
            $version->update([
                'status' => 'PUBLISHED',
                'published_at' => now(),
            ]);

            $version->testBundle?->update(['status' => 'PUBLISHED']);

            $exercise->update([
                'active_version_id' => $version->id,
                'publication_status' => 'PUBLISHED',
                'published_at' => now(),
                'archived_at' => null,
                'difficulty' => $version->difficulty,
            ]);

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'action' => 'exercise.published',
                'auditable_type' => Exercise::class,
                'auditable_id' => $exercise->id,
                'metadata' => ['version_id' => $version->id, 'version_number' => $version->version_number],
                'occurred_at' => now(),
            ]);
        });

        return redirect()->route('admin.exercises.index')->with('status', 'Exercise published.');
    }

    public function archive(Request $request, Exercise $exercise): RedirectResponse
    {
        DB::transaction(function () use ($request, $exercise): void {
            $exercise->update([
                'publication_status' => 'ARCHIVED',
                'archived_at' => now(),
            ]);

            $exercise->activeVersion?->update([
                'status' => 'ARCHIVED',
                'archived_at' => now(),
            ]);

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'action' => 'exercise.archived',
                'auditable_type' => Exercise::class,
                'auditable_id' => $exercise->id,
                'metadata' => ['active_version_id' => $exercise->active_version_id],
                'occurred_at' => now(),
            ]);
        });

        return back()->with('status', 'Exercise archived.');
    }

    public function importForm(): View
    {
        return view('admin.exercises.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['payload' => ['required', 'json']]);
        $rows = json_decode($request->input('payload'), true);

        if (! is_array($rows)) {
            throw ValidationException::withMessages(['payload' => 'Import payload must be a JSON array.']);
        }

        $errors = [];
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, $this->importRules());
            if ($validator->fails()) {
                $errors['row '.($index + 1)] = implode(' ', $validator->errors()->all());
            }
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        foreach ($rows as $row) {
            $data = $this->normaliseImportedRow($row);
            DB::transaction(function () use ($request, $data): void {
                $exercise = Exercise::create([
                    'course_id' => $data['course_id'],
                    'lesson_id' => $data['lesson_id'],
                    'created_by' => $request->user()->id,
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'summary' => $data['summary'],
                    'difficulty' => $data['difficulty'],
                    'publication_status' => 'DRAFT',
                ]);

                $this->storeDraftVersion($exercise, $data, $request->user()->id);
            });
        }

        return redirect()->route('admin.exercises.index')->with('status', count($rows).' exercise draft(s) imported.');
    }

    private function form(Exercise $exercise): View
    {
        $latest = $exercise->exists ? $exercise->versions()->latest('version_number')->with('testBundle.testCases')->first() : null;

        return view('admin.exercises.form', [
            'exercise' => $exercise,
            'latest' => $latest,
            'courses' => Course::orderBy('title')->get(),
            'lessons' => Lesson::orderBy('title')->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?Exercise $exercise = null): array
    {
        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('exercises', 'slug')->ignore($exercise)],
            'summary' => ['required', 'string', 'max:255'],
            'description_markdown' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(Exercise::DIFFICULTIES)],
            'concept_tags' => ['required', 'string'],
            'constraints' => ['nullable', 'string'],
            'supported_languages' => ['required', 'array', 'min:1'],
            'supported_languages.*' => ['required', Rule::in(['python', 'javascript', 'typescript'])],
            'starter_code_python' => ['nullable', 'string'],
            'starter_code_javascript' => ['nullable', 'string'],
            'starter_code_typescript' => ['nullable', 'string'],
            'function_signature_python' => ['nullable', 'string'],
            'function_signature_javascript' => ['nullable', 'string'],
            'function_signature_typescript' => ['nullable', 'string'],
            'visible_examples_json' => ['nullable', 'json'],
            'visible_tests_json' => ['required', 'json'],
            'hidden_tests_json' => ['required', 'json'],
            'hints_text' => ['nullable', 'string'],
            'official_solution_python' => ['nullable', 'string'],
            'official_solution_javascript' => ['nullable', 'string'],
            'official_solution_typescript' => ['nullable', 'string'],
            'explanation_markdown' => ['nullable', 'string'],
            'time_limit_ms' => ['required', 'integer', 'min:100', 'max:30000'],
            'memory_limit_mb' => ['required', 'integer', 'min:16', 'max:1024'],
            'points' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        return $this->normalisePayload($validated);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalisePayload(array $validated): array
    {
        $languages = array_values(array_unique($validated['supported_languages']));
        $starter = [];
        $signatures = [];
        $solutions = [];

        foreach (['python', 'javascript', 'typescript'] as $language) {
            if (in_array($language, $languages, true)) {
                $starter[$language] = $validated['starter_code_'.$language] ?? '';
                $signatures[$language] = $validated['function_signature_'.$language] ?? '';
                $solutions[$language] = $validated['official_solution_'.$language] ?? '';
            }
        }

        $conceptTags = collect(explode(',', $validated['concept_tags']))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values();

        if ($conceptTags->isEmpty()) {
            throw ValidationException::withMessages(['concept_tags' => 'At least one concept tag is required.']);
        }

        return [
            'course_id' => $validated['course_id'] ?? null,
            'lesson_id' => $validated['lesson_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'summary' => $validated['summary'],
            'description_markdown' => $validated['description_markdown'],
            'difficulty' => $validated['difficulty'],
            'concept_tags' => $conceptTags->all(),
            'constraints' => collect(preg_split('/\r\n|\r|\n/', $validated['constraints'] ?? ''))->map(fn ($line) => trim($line))->filter()->values()->all(),
            'supported_languages' => $languages,
            'starter_code_by_language' => $starter,
            'function_signature_by_language' => $signatures,
            'visible_examples' => json_decode($validated['visible_examples_json'] ?? '[]', true) ?: [],
            'visible_tests' => json_decode($validated['visible_tests_json'], true) ?: [],
            'hidden_tests' => json_decode($validated['hidden_tests_json'], true) ?: [],
            'hints' => collect(preg_split('/\r\n|\r|\n/', $validated['hints_text'] ?? ''))->map(fn ($line, $index) => ['order' => $index + 1, 'content_markdown' => trim($line)])->filter(fn ($hint) => $hint['content_markdown'] !== '')->values()->all(),
            'official_solutions_by_language' => $solutions,
            'explanation_markdown' => $validated['explanation_markdown'] ?? null,
            'time_limit_ms' => (int) $validated['time_limit_ms'],
            'memory_limit_mb' => (int) $validated['memory_limit_mb'],
            'points' => (int) $validated['points'],
        ];
    }

    private function storeDraftVersion(Exercise $exercise, array $data, int $adminId): ExerciseVersion
    {
        $conceptIds = collect($data['concept_tags'])->map(function (string $tag) {
            return Concept::firstOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag],
            )->id;
        });

        $exercise->concepts()->sync($conceptIds);
        $versionNumber = ((int) $exercise->versions()->max('version_number')) + 1;

        $version = $exercise->versions()->create([
            'created_by' => $adminId,
            'version_number' => $versionNumber,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description_markdown' => $data['description_markdown'],
            'difficulty' => $data['difficulty'],
            'constraints' => $data['constraints'],
            'visible_examples' => $data['visible_examples'],
            'supported_languages' => $data['supported_languages'],
            'starter_code_by_language' => $data['starter_code_by_language'],
            'function_signature_by_language' => $data['function_signature_by_language'],
            'hints' => $data['hints'],
            'official_solutions_by_language' => $data['official_solutions_by_language'],
            'explanation_markdown' => $data['explanation_markdown'],
            'time_limit_ms' => $data['time_limit_ms'],
            'memory_limit_mb' => $data['memory_limit_mb'],
            'points' => $data['points'],
            'status' => 'DRAFT',
        ]);

        foreach ($data['starter_code_by_language'] as $language => $code) {
            $version->starterCodes()->create(['language' => $language, 'code' => $code]);
        }

        foreach ($data['hints'] as $hint) {
            $version->hintRecords()->create([
                'hint_order' => $hint['order'],
                'content_markdown' => $hint['content_markdown'],
            ]);
        }

        foreach ($data['official_solutions_by_language'] as $language => $code) {
            if (trim($code) !== '') {
                $version->officialSolutions()->create([
                    'language' => $language,
                    'code' => $code,
                    'explanation_markdown' => $data['explanation_markdown'],
                ]);
            }
        }

        $bundle = $version->testBundle()->create([
            'version_label' => 'v'.$versionNumber,
            'status' => 'DRAFT',
            'checksum' => hash('sha256', json_encode([$data['visible_tests'], $data['hidden_tests']])),
        ]);

        foreach ([['VISIBLE', $data['visible_tests']], ['HIDDEN', $data['hidden_tests']]] as [$visibility, $tests]) {
            foreach ($tests as $index => $test) {
                $bundle->testCases()->create([
                    'visibility' => $visibility,
                    'name' => $test['name'] ?? ($visibility.' test '.($index + 1)),
                    'input' => $test['input'] ?? null,
                    'expected_output' => $test['expected_output'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return $version;
    }

    /**
     * @return array<string, mixed>
     */
    private function importRules(): array
    {
        return [
            'title' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:exercises,slug'],
            'summary' => ['required', 'string'],
            'description_markdown' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(Exercise::DIFFICULTIES)],
            'concepts' => ['required', 'array', 'min:1'],
            'supported_languages' => ['required', 'array', 'min:1'],
            'visible_tests' => ['required', 'array', 'min:1'],
            'hidden_tests' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseImportedRow(array $row): array
    {
        return [
            'course_id' => null,
            'lesson_id' => null,
            'title' => $row['title'],
            'slug' => $row['slug'],
            'summary' => $row['summary'],
            'description_markdown' => $row['description_markdown'],
            'difficulty' => $row['difficulty'],
            'concept_tags' => $row['concepts'],
            'constraints' => $row['constraints'] ?? [],
            'supported_languages' => $row['supported_languages'],
            'starter_code_by_language' => $row['starter_code_by_language'] ?? [],
            'function_signature_by_language' => $row['function_signature_by_language'] ?? [],
            'visible_examples' => $row['visible_examples'] ?? [],
            'visible_tests' => $row['visible_tests'],
            'hidden_tests' => $row['hidden_tests'],
            'hints' => collect($row['hints'] ?? [])->values()->map(fn ($hint, $index) => [
                'order' => $hint['order'] ?? $index + 1,
                'content_markdown' => $hint['content_markdown'] ?? (string) $hint,
            ])->all(),
            'official_solutions_by_language' => $row['official_solutions_by_language'] ?? [],
            'explanation_markdown' => $row['explanation_markdown'] ?? null,
            'time_limit_ms' => $row['time_limit_ms'] ?? 2000,
            'memory_limit_mb' => $row['memory_limit_mb'] ?? 128,
            'points' => $row['points'] ?? 10,
        ];
    }
}
