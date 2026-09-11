<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.lessons.index', [
            'lessons' => Lesson::with('course')
                ->when($request->integer('course_id'), fn ($query, $courseId) => $query->where('course_id', $courseId))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lessons.form', [
            'lesson' => new Lesson(),
            'courses' => Course::orderBy('title')->get(),
            'modules' => Module::with('course')->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLesson($request);
        $lesson = Lesson::create($data + ['published_at' => $data['publication_status'] === 'PUBLISHED' ? now() : null]);
        $this->snapshot($lesson);

        return redirect()->route('admin.lessons.index')->with('status', 'Lesson saved.');
    }

    public function edit(Lesson $lesson): View
    {
        return view('admin.lessons.form', [
            'lesson' => $lesson,
            'courses' => Course::orderBy('title')->get(),
            'modules' => Module::with('course')->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $this->validateLesson($request, $lesson);
        $lesson->update($data + [
            'published_at' => $data['publication_status'] === 'PUBLISHED' ? ($lesson->published_at ?? now()) : null,
            'archived_at' => $data['publication_status'] === 'ARCHIVED' ? now() : null,
        ]);
        $this->snapshot($lesson);

        return redirect()->route('admin.lessons.index')->with('status', 'Lesson updated.');
    }

    private function validateLesson(Request $request, ?Lesson $lesson = null): array
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'module_id' => [
                'nullable',
                Rule::exists('modules', 'id')->where(fn ($query) => $query->where('course_id', $request->input('course_id'))),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('lessons', 'slug')->ignore($lesson)],
            'summary' => ['required', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'body_markdown' => ['required', 'string'],
            'code_example' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'publication_status' => ['required', Rule::in(['DRAFT', 'PUBLISHED', 'ARCHIVED'])],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        return $data;
    }

    private function snapshot(Lesson $lesson): void
    {
        $lesson->versions()->create([
            'version_number' => $lesson->versions()->max('version_number') + 1,
            'title' => $lesson->title,
            'summary' => $lesson->summary,
            'video_url' => $lesson->video_url,
            'duration_minutes' => $lesson->duration_minutes,
            'body_markdown' => $lesson->body_markdown,
            'code_example' => $lesson->code_example,
            'status' => $lesson->publication_status,
            'published_at' => $lesson->published_at,
        ]);
    }
}
