<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.modules.index', [
            'modules' => Module::with('course')
                ->when($request->integer('course_id'), fn ($query, $courseId) => $query->where('course_id', $courseId))
                ->orderBy('course_id')
                ->orderBy('sort_order')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.modules.form', [
            'module' => new Module(),
            'courses' => Course::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Module::create($this->validatedModule($request));

        return redirect()->route('admin.modules.index')->with('status', 'Module saved.');
    }

    public function edit(Module $module): View
    {
        return view('admin.modules.form', [
            'module' => $module,
            'courses' => Course::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $module->update($this->validatedModule($request, $module));

        return redirect()->route('admin.modules.index')->with('status', 'Module updated.');
    }

    private function validatedModule(Request $request, ?Module $module = null): array
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('modules', 'slug')->where(fn ($query) => $query->where('course_id', $request->input('course_id')))->ignore($module),
            ],
            'summary' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        return $data;
    }
}
