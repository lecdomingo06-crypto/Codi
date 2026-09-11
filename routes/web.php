<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ExerciseController as AdminExerciseController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseWorkspaceController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/courses', [CatalogController::class, 'courses'])->name('courses.index');
    Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course:slug}/enroll', [CatalogController::class, 'enroll'])->name('courses.enroll');
    Route::get('/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/lessons/{lesson:slug}/complete', [LessonController::class, 'complete'])->name('lessons.complete');
    Route::get('/exercises/{exercise:slug}', [ExerciseWorkspaceController::class, 'show'])->name('exercises.show');
    Route::post('/exercises/{exercise:slug}/check', [ExerciseWorkspaceController::class, 'check'])->name('exercises.check');
    Route::post('/exercises/{exercise:slug}/run', [ExerciseWorkspaceController::class, 'run'])->name('exercises.run');
    Route::post('/exercises/{exercise:slug}/submit', [ExerciseWorkspaceController::class, 'submit'])->name('exercises.submit');
    Route::get('/submissions/{submission}', [ExerciseWorkspaceController::class, 'submission'])->name('submissions.show');
    Route::get('/progress', [ProgressController::class, 'show'])->name('progress.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('/api/v1')->group(function (): void {
        Route::get('/me/streak', [ProgressController::class, 'streak']);
        Route::get('/me/activity-calendar', [ProgressController::class, 'calendar']);
        Route::get('/courses', [CatalogController::class, 'index']);
        Route::post('/runs', function () {
            abort(308, 'Use POST /exercises/{slug}/run in this Laravel MVP.');
        });
        Route::post('/submissions', function () {
            abort(308, 'Use POST /exercises/{slug}/submit in this Laravel MVP.');
        });
    });
});

Route::middleware(['auth', 'role:ADMIN'])->prefix('/admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('courses', AdminCourseController::class)->except(['show', 'destroy']);
    Route::resource('modules', AdminModuleController::class)->except(['show', 'destroy']);
    Route::resource('lessons', AdminLessonController::class)->except(['show', 'destroy']);
    Route::get('/exercises/import', [AdminExerciseController::class, 'importForm'])->name('exercises.import.form');
    Route::post('/exercises/import', [AdminExerciseController::class, 'import'])->name('exercises.import');
    Route::resource('exercises', AdminExerciseController::class)->except(['show', 'destroy']);
    Route::get('/exercises/{exercise}/versions/{version}/preview', [AdminExerciseController::class, 'preview'])->name('exercises.preview');
    Route::post('/exercises/{exercise}/versions/{version}/publish', [AdminExerciseController::class, 'publish'])->name('exercises.publish');
    Route::post('/exercises/{exercise}/archive', [AdminExerciseController::class, 'archive'])->name('exercises.archive');
    Route::view('/users', 'admin.users.index')->name('users.index');
    Route::view('/analytics', 'admin.analytics.index')->name('analytics.index');
});
