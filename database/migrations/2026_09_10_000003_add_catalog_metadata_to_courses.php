<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('category')->default('Programming')->after('summary');
            $table->unsignedInteger('duration_minutes')->nullable()->after('category');
            $table->string('difficulty', 16)->default('EASY')->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn(['category', 'duration_minutes', 'difficulty']);
        });
    }
};