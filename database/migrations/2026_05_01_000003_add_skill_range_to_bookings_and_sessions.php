<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('skill_min')->nullable()->after('skill_level')->comment('Min skill level to join (1-7)');
            $table->unsignedTinyInteger('skill_max')->nullable()->after('skill_min')->comment('Max skill level to join (1-7)');
        });

        Schema::table('academy_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('skill_min')->nullable()->after('skill_level')->comment('Min skill level to enroll (1-7)');
            $table->unsignedTinyInteger('skill_max')->nullable()->after('skill_min')->comment('Max skill level to enroll (1-7)');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['skill_min', 'skill_max']);
        });
        Schema::table('academy_sessions', function (Blueprint $table) {
            $table->dropColumn(['skill_min', 'skill_max']);
        });
    }
};
