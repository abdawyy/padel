<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('club_saas_subscription_id')
                ->nullable()
                ->after('academy_session_id')
                ->constrained('club_saas_subscriptions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['club_saas_subscription_id']);
            $table->dropColumn('club_saas_subscription_id');
        });
    }
};
