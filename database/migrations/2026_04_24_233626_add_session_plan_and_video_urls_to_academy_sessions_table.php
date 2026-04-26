<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academy_sessions', function (Blueprint $table) {
            $table->text('session_plan')->nullable()->after('notes');
            $table->json('video_urls')->nullable()->after('video_url');
        });

        DB::table('academy_sessions')
            ->whereNotNull('video_url')
            ->update([
                'video_urls' => DB::raw('JSON_ARRAY(video_url)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academy_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_plan', 'video_urls']);
        });
    }
};
