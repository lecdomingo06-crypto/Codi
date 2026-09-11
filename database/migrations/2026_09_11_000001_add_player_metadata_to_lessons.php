<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            $table->string('video_url')->nullable()->after('summary');
            $table->unsignedInteger('duration_minutes')->nullable()->after('video_url');
        });

        Schema::table('lesson_versions', function (Blueprint $table): void {
            $table->string('video_url')->nullable()->after('summary');
            $table->unsignedInteger('duration_minutes')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_versions', function (Blueprint $table): void {
            $table->dropColumn(['video_url', 'duration_minutes']);
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropColumn(['video_url', 'duration_minutes']);
        });
    }
};
