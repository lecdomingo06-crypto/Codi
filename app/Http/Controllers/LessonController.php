<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Lesson $lesson): View
    {
        abort_unless($lesson->publication_status === 'PUBLISHED', 404);

        $lesson->load(['course', 'module', 'exercises' => fn ($query) => $query->where('publication_status', 'PUBLISHED')]);

        return view('lessons.show', [
            'lesson' => $lesson,
            'isCompleted' => DB::table('lesson_progress')
                ->where('user_id', request()->user()->id)
                ->where('lesson_id', $lesson->id)
                ->where('status', 'COMPLETED')
                ->exists(),
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->publication_status === 'PUBLISHED', 404);
        abort_unless($request->user()->isUser(), 403);

        DB::table('lesson_progress')->updateOrInsert(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['status' => 'COMPLETED', 'completed_at' => now(), 'updated_at' => now(), 'created_at' => now()],
        );

        return back()->with('status', 'Lesson marked complete.');
    }
}
