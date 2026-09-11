@php
    $isAdmin = $isAdmin ?? auth()->user()->isAdmin();
    $isEnrolled = $isEnrolled ?? auth()->user()->enrollments()->where('course_id', $course->id)->exists();
    $currentModule = $lesson?->module;
    $moduleLessons = $currentModule
        ? $currentModule->lessons->where('publication_status', 'PUBLISHED')->sortBy('sort_order')->values()
        : $courseLessons;
    $standaloneLessons = $courseLessons->whereNull('module_id')->values();
    $sidebarStandaloneLessons = $course->modules->isEmpty() ? $courseLessons : $standaloneLessons;
@endphp

<section class="course-player-shell">
    <aside class="course-player-sidebar" aria-label="Course lessons">
        <nav class="course-player-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('courses.index') }}">Courses</a>
            <span aria-hidden="true">/</span>
            <strong>{{ $course->title }}</strong>
        </nav>

        <div class="course-player-progress">
            <div>
                <span>Progress</span>
                <strong>{{ $completedLessonsCount }} / {{ $totalLessons }} &middot; {{ $progressPercent }}%</strong>
            </div>
            <span class="course-player-progress__bar"><b style="width: {{ $progressPercent }}%"></b></span>
        </div>

        <div class="course-player-nav">
            @if($course->modules->isNotEmpty())
                @foreach($course->modules as $module)
                    <section class="course-player-module">
                        <header>
                            <h2>{{ $module->title }}</h2>
                            <span>{{ $module->lessons->count() }}</span>
                        </header>
                        @foreach($module->lessons as $navLesson)
                            <a class="{{ $lesson && $lesson->id === $navLesson->id ? 'is-active' : '' }}" href="{{ route('lessons.show', $navLesson) }}">
                                <span class="lesson-status {{ $completedLessonIds->contains($navLesson->id) ? 'is-complete' : '' }}"></span>
                                <span>
                                    <strong>{{ $navLesson->title }}</strong>
                                    <small>{{ $navLesson->duration_minutes ? $navLesson->duration_minutes.'m' : 'Lesson' }}</small>
                                </span>
                            </a>
                        @endforeach
                    </section>
                @endforeach
            @endif

            @if($course->modules->isEmpty() || $standaloneLessons->isNotEmpty())
                <section class="course-player-module">
                    <header>
                        <h2>Lessons</h2>
                        <span>{{ $sidebarStandaloneLessons->count() }}</span>
                    </header>
                    @foreach($sidebarStandaloneLessons as $navLesson)
                        <a class="{{ $lesson && $lesson->id === $navLesson->id ? 'is-active' : '' }}" href="{{ route('lessons.show', $navLesson) }}">
                            <span class="lesson-status {{ $completedLessonIds->contains($navLesson->id) ? 'is-complete' : '' }}"></span>
                            <span>
                                <strong>{{ $navLesson->title }}</strong>
                                <small>{{ $navLesson->duration_minutes ? $navLesson->duration_minutes.'m' : 'Lesson' }}</small>
                            </span>
                        </a>
                    @endforeach
                </section>
            @endif
        </div>
    </aside>

    <main class="course-player-main">
        @if($lesson)
            <section class="course-video-card">
                <div class="course-video-frame">
                    @if($lesson->videoEmbedUrl())
                        <iframe src="{{ $lesson->videoEmbedUrl() }}" title="{{ $lesson->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    @else
                        <div class="course-video-placeholder">
                            <span>{{ $course->title }}</span>
                            <strong>{{ $lesson->title }}</strong>
                            <p>{{ $lesson->summary }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="lesson-action-card">
                @if(auth()->user()->isUser())
                    <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                        @csrf
                        <button type="submit" class="lesson-complete-button {{ $isCompleted ? 'is-complete' : '' }}">
                            <span aria-hidden="true"></span>
                            {{ $isCompleted ? 'Completed' : 'Mark Complete' }}
                        </button>
                    </form>
                @endif

                <div class="lesson-action-card__nav">
                    @if($lesson->code_example)
                        <a class="button button--secondary" href="#lesson-code">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m8 9-4 3 4 3m8-6 4 3-4 3M14 4l-4 16"/></svg>
                            View Code
                        </a>
                    @endif
                    <a class="lesson-nav-button {{ $previousLesson ? '' : 'is-disabled' }}" href="{{ $previousLesson ? route('lessons.show', $previousLesson) : '#' }}" aria-disabled="{{ $previousLesson ? 'false' : 'true' }}" title="Previous lesson">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <a class="lesson-nav-button {{ $nextLesson ? '' : 'is-disabled' }}" href="{{ $nextLesson ? route('lessons.show', $nextLesson) : '#' }}" aria-disabled="{{ $nextLesson ? 'false' : 'true' }}" title="Next lesson">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>

                <div class="lesson-rating">
                    <span>How was this lesson?</span>
                    <span aria-label="Five star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </div>
            </section>

            <div class="course-player-content">
                <article class="lesson-reading">
                    <header>
                        <span>Lesson {{ $currentLessonNumber }} of {{ $totalLessons }}</span>
                        <h1>{{ $lesson->title }}</h1>
                        <p>{{ $lesson->summary }}</p>
                    </header>

                    <section id="lesson-notes" class="lesson-notes">
                        {!! nl2br(e($lesson->body_markdown)) !!}
                    </section>

                    @if($lesson->code_example)
                        <section class="lesson-code-panel" id="lesson-code">
                            <header>
                                <h2>Code</h2>
                                <span>{{ $lesson->title }}</span>
                            </header>
                            <pre><code>{{ $lesson->code_example }}</code></pre>
                        </section>
                    @endif

                    <section class="lesson-module-list" id="module-lessons">
                        <header>
                            <h2>{{ $currentModule ? $currentModule->title : 'Course lessons' }}</h2>
                            <span>{{ $moduleLessons->count() }} {{ $moduleLessons->count() === 1 ? 'lesson' : 'lessons' }}</span>
                        </header>
                        <div>
                            @foreach($moduleLessons as $moduleLesson)
                                <a class="{{ $moduleLesson->id === $lesson->id ? 'is-active' : '' }}" href="{{ route('lessons.show', $moduleLesson) }}">
                                    <span class="lesson-status {{ $completedLessonIds->contains($moduleLesson->id) ? 'is-complete' : '' }}"></span>
                                    <strong>{{ $moduleLesson->title }}</strong>
                                    <small>{{ $moduleLesson->duration_minutes ? $moduleLesson->duration_minutes.'m' : 'Lesson' }}</small>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    <section class="suggested-problems" id="suggested-problems">
                        <header>
                            <h2>Suggested Problems</h2>
                            <span>{{ $suggestedExercises->count() }}</span>
                        </header>
                        <div class="suggested-problems-table">
                            <div class="suggested-problems-head">
                                <span>Status</span>
                                <span>Problem</span>
                                <span>Difficulty</span>
                            </div>
                            @forelse($suggestedExercises as $exercise)
                                <a class="suggested-problem-row" href="{{ route('exercises.show', $exercise) }}">
                                    <span class="lesson-status"></span>
                                    <span><strong>{{ $exercise->title }}</strong><small>{{ $exercise->summary }}</small></span>
                                    <x-difficulty-badge :difficulty="$exercise->difficulty" />
                                </a>
                            @empty
                                <p>No suggested problems yet.</p>
                            @endforelse
                        </div>
                    </section>
                </article>

                <aside class="course-page-toc" aria-label="On this page">
                    <span>On this page</span>
                    <a href="#lesson-notes">Notes</a>
                    @if($lesson->code_example)
                        <a href="#lesson-code">Code</a>
                    @endif
                    <a href="#module-lessons">Module lessons</a>
                    <a href="#suggested-problems">Suggested Problems</a>
                </aside>
            </div>
        @else
            <section class="panel stack">
                <div>
                    <p class="eyebrow">Course</p>
                    <h1>{{ $course->title }}</h1>
                    <p>{{ $course->summary }}</p>
                </div>
                <p>No published lessons are available in this course yet.</p>
                @if(!$isAdmin && !$isEnrolled)
                    <form method="POST" action="{{ route('courses.enroll', $course) }}">
                        @csrf
                        <button type="submit">Enroll in course</button>
                    </form>
                @endif
            </section>
        @endif
    </main>
</section>
