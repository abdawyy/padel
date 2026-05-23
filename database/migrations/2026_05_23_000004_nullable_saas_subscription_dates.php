<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_saas_subscriptions', function (Blueprint $table) {
            $table->date('starts_at')->nullable()->change();
            $table->date('ends_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('club_saas_subscriptions', function (Blueprint $table) {
            $table->date('starts_at')->nullable(false)->change();
            $table->date('ends_at')->nullable(false)->change();
        });
    }
};
