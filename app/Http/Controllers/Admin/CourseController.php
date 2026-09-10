<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('admin.courses.index', ['courses' => Course::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.courses.form', ['course' => new Course()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCourse($request);
        $course = Course::create($validated + [
            'created_by' => $request->user()->id,
            'published_at' => $validated['publication_status'] === 'PUBLISHED' ? now() : null,
        ]);

        $course->versions()->create([
            'version_number' => 1,
            'title' => $course->title,
            'summary' => $course->summary,
            'description_markdown' => $course->description_markdown,
            'status' => $course->publication_status,
            'published_at' => $course->published_at,
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Course saved.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.form', ['course' => $course]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $this->validateCourse($request, $course);
        $course->update($validated + [
            'published_at' => $validated['publication_status'] === 'PUBLISHED' ? ($course->published_at ?? now()) : null,
            'archived_at' => $validated['publication_status'] === 'ARCHIVED' ? now() : null,
        ]);

        $course->versions()->create([
            'version_number' => $course->versions()->max('version_number') + 1,
            'title' => $course->title,
            'summary' => $course->summary,
            'description_markdown' => $course->description_markdown,
            'status' => $course->publication_status,
            'published_at' => $course->published_at,
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Course updated.');
    }

    private function validateCourse(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('courses', 'slug')->ignore($course)],
            'summary' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:120'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'difficulty' => ['required', Rule::in(['EASY', 'MEDIUM', 'HARD'])],
            'description_markdown' => ['nullable', 'string'],
            'publication_status' => ['required', Rule::in(['DRAFT', 'PUBLISHED', 'ARCHIVED'])],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        return $data;
    }
}
