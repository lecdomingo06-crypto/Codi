<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 16)->default('USER')->after('password')->index();
            $table->string('status', 16)->default('ACTIVE')->after('role')->index();
            $table->string('timezone', 64)->default('UTC')->after('status');
            $table->unsignedInteger('points')->default(0)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'status', 'timezone', 'points']);
        });
    }
};
