<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            // pending → approved → rejected
            $table->string('registration_status')->default('approved')->after('subscription_status');
            $table->text('rejection_reason')->nullable()->after('registration_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['registration_status', 'rejection_reason', 'approved_at', 'approved_by']);
        });
    }
};
