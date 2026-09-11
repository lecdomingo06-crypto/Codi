<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CoursePlayerService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Course $course, User $user, ?Lesson $currentLesson = null): array
    {
        $course->load([
            'modules' => fn ($query) => $query->orderBy('sort_order')->orderBy('title'),
            'modules.lessons' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->orderBy('sort_order')->orderBy('title'),
            'lessons' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->orderBy('sort_order')->orderBy('title'),
            'exercises' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->with('concepts'),
        ]);

        $course->setRelation(
            'modules',
            $course->modules->filter(fn ($module) => $module->lessons->isNotEmpty())->values(),
        );

        $moduleLessons = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->values();

        $standaloneLessons = $course->lessons
            ->whereNull('module_id')
            ->values();

        $orderedLessons = $moduleLessons
            ->concat($standaloneLessons)
            ->unique('id')
            ->values();

        if ($currentLesson && ($currentLesson->course_id !== $course->id || $currentLesson->publication_status !== 'PUBLISHED')) {
            $currentLesson = null;
        }

        $currentLesson ??= $orderedLessons->first();

        if ($currentLesson) {
            $currentLesson->loadMissing([
                'course',
                'module',
                'exercises' => fn ($query) => $query->where('publication_status', 'PUBLISHED')->with('concepts'),
            ]);

            if ($currentLesson->module_id) {
                $loadedModule = $course->modules->firstWhere('id', $currentLesson->module_id);

                if ($loadedModule) {
                    $currentLesson->setRelation('module', $loadedModule);
                }
            }
        }

        $completedLessonIds = DB::table('lesson_progress')
            ->where('user_id', $user->id)
            ->where('status', 'COMPLETED')
            ->whereIn('lesson_id', $orderedLessons->pluck('id'))
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id);

        $currentIndex = $currentLesson
            ? $orderedLessons->search(fn (Lesson $lesson) => $lesson->id === $currentLesson->id)
            : false;

        $currentIndex = $currentIndex === false ? 0 : (int) $currentIndex;
        $totalLessons = $orderedLessons->count();
        $completedLessonsCount = $completedLessonIds->count();

        return [
            'course' => $course,
            'lesson' => $currentLesson,
            'courseLessons' => $orderedLessons,
            'completedLessonIds' => $completedLessonIds,
            'completedLessonsCount' => $completedLessonsCount,
            'totalLessons' => $totalLessons,
            'progressPercent' => $totalLessons > 0 ? (int) round(($completedLessonsCount / $totalLessons) * 100) : 0,
            'currentLessonNumber' => $currentLesson ? $currentIndex + 1 : 0,
            'previousLesson' => $currentIndex > 0 ? $orderedLessons->get($currentIndex - 1) : null,
            'nextLesson' => $currentIndex < $totalLessons - 1 ? $orderedLessons->get($currentIndex + 1) : null,
            'isCompleted' => $currentLesson ? $completedLessonIds->contains($currentLesson->id) : false,
            'suggestedExercises' => $currentLesson && $currentLesson->exercises->isNotEmpty()
                ? $currentLesson->exercises
                : $course->exercises,
        ];
    }
}
