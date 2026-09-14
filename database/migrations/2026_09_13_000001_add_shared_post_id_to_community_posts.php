<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->foreignId('shared_post_id')
                ->nullable()
                ->constrained('community_posts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shared_post_id');
        });
    }
};
