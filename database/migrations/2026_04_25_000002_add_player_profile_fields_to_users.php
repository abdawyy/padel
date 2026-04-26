<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Skill level 1–7 (WPT padel scale: 1=Beginner … 7=Elite/Pro)
            $table->unsignedTinyInteger('skill_level')->nullable()->default(null)->after('is_active');
            $table->date('date_of_birth')->nullable()->after('skill_level');
            $table->string('preferred_sport', 30)->default('padel')->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['skill_level', 'date_of_birth', 'preferred_sport']);
        });
    }
};
