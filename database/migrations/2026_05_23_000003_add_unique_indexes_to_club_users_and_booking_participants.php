<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_participants', function (Blueprint $table) {
            $table->unique(['booking_id', 'user_id']);
        });

        Schema::table('club_users', function (Blueprint $table) {
            $table->unique(['club_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('booking_participants', function (Blueprint $table) {
            $table->dropUnique(['booking_id', 'user_id']);
        });

        Schema::table('club_users', function (Blueprint $table) {
            $table->dropUnique(['club_id', 'user_id']);
        });
    }
};
