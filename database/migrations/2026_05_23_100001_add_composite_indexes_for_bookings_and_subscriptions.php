<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['court_id', 'status', 'start_time', 'end_time'], 'bookings_court_status_window_index');
        });

        Schema::table('club_saas_subscriptions', function (Blueprint $table) {
            $table->index(['club_id', 'status', 'ends_at'], 'club_saas_subscriptions_club_status_ends_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_court_status_window_index');
        });

        Schema::table('club_saas_subscriptions', function (Blueprint $table) {
            $table->dropIndex('club_saas_subscriptions_club_status_ends_index');
        });
    }
};
