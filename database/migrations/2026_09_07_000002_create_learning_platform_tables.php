<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concepts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('concept_prerequisites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('concept_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prerequisite_concept_id')->constrained('concepts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['concept_id', 'prerequisite_concept_id']);
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary');
            $table->longText('description_markdown')->nullable();
            $table->string('publication_status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('course_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->string('summary');
            $table->longText('description_markdown')->nullable();
            $table->string('status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'version_number']);
        });

        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->unique(['course_id', 'slug']);
        });

        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary');
            $table->longText('body_markdown');
            $table->longText('code_example')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->string('publication_status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lesson_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->string('summary');
            $table->longText('body_markdown');
            $table->longText('code_example')->nullable();
            $table->string('status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['lesson_id', 'version_number']);
        });

        Schema::create('exercises', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('active_version_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary');
            $table->string('difficulty', 16)->index();
            $table->string('publication_status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exercise_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->string('summary');
            $table->longText('description_markdown');
            $table->string('difficulty', 16)->index();
            $table->json('constraints')->nullable();
            $table->json('visible_examples')->nullable();
            $table->json('supported_languages');
            $table->json('starter_code_by_language')->nullable();
            $table->json('function_signature_by_language')->nullable();
            $table->json('hints')->nullable();
            $table->json('official_solutions_by_language')->nullable();
            $table->longText('explanation_markdown')->nullable();
            $table->unsignedInteger('time_limit_ms')->default(2000);
            $table->unsignedInteger('memory_limit_mb')->default(128);
            $table->unsignedInteger('points')->default(10);
            $table->string('status', 16)->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['exercise_id', 'version_number']);
        });

        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_version_id')->nullable()->constrained('lesson_versions')->nullOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24);
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('exercise_concepts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('concept_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['exercise_id', 'concept_id']);
        });

        Schema::create('starter_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->string('language', 32);
            $table->longText('code');
            $table->timestamps();
            $table->unique(['exercise_version_id', 'language']);
        });

        Schema::create('hints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('hint_order');
            $table->longText('content_markdown');
            $table->timestamps();
            $table->unique(['exercise_version_id', 'hint_order']);
        });

        Schema::create('official_solutions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->string('language', 32);
            $table->longText('code');
            $table->longText('explanation_markdown')->nullable();
            $table->timestamps();
            $table->unique(['exercise_version_id', 'language']);
        });

        Schema::create('test_bundles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->string('version_label')->default('v1');
            $table->string('status', 16)->default('DRAFT')->index();
            $table->string('storage_driver')->default('database');
            $table->string('checksum')->nullable();
            $table->timestamps();
        });

        Schema::create('test_cases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('test_bundle_id')->constrained()->cascadeOnDelete();
            $table->string('visibility', 16)->index();
            $table->string('name');
            $table->longText('input')->nullable();
            $table->longText('expected_output')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('STARTED')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });

        Schema::create('user_concept_masteries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('concept_id')->constrained()->cascadeOnDelete();
            $table->decimal('mastery_score', 5, 2)->default(0);
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'concept_id']);
        });

        Schema::create('attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_version_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('hint_reveals')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->string('language', 32);
            $table->string('runtime_version')->default('local-simulated-judge');
            $table->longText('source_code');
            $table->string('status', 24)->default('QUEUED')->index();
            $table->string('verdict', 32)->nullable()->index();
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->unsignedInteger('memory_kb')->nullable();
            $table->text('output')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_version_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key', 96);
            $table->string('language', 32);
            $table->string('runtime_version')->default('local-simulated-judge');
            $table->unsignedInteger('time_limit_ms');
            $table->unsignedInteger('memory_limit_mb');
            $table->longText('source_code');
            $table->string('status', 24)->default('QUEUED')->index();
            $table->string('verdict', 32)->nullable()->index();
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->unsignedInteger('memory_kb')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['user_id', 'idempotency_key']);
        });

        Schema::create('test_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('run_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('test_name');
            $table->string('visibility', 16)->index();
            $table->string('verdict', 32);
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->unsignedInteger('memory_kb')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_exercise_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('activity_date');
            $table->unsignedInteger('answer_count')->default(0);
            $table->unsignedInteger('accepted_answer_count')->default(0);
            $table->timestamp('first_answer_at')->nullable();
            $table->timestamp('last_answer_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'activity_date']);
        });

        Schema::create('user_streaks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_qualifying_activity_date')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('achievements', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at');
            $table->timestamps();
            $table->unique(['user_id', 'achievement_id']);
        });

        Schema::create('learning_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 48)->index();
            $table->string('idempotency_key', 120);
            $table->foreignId('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exercise_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['user_id', 'event_type', 'idempotency_key']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('learning_events');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('daily_exercise_activities');
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('runs');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('user_concept_masteries');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('test_cases');
        Schema::dropIfExists('test_bundles');
        Schema::dropIfExists('official_solutions');
        Schema::dropIfExists('hints');
        Schema::dropIfExists('starter_codes');
        Schema::dropIfExists('exercise_concepts');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('exercise_versions');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('lesson_versions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('course_versions');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('concept_prerequisites');
        Schema::dropIfExists('concepts');
    }
};
