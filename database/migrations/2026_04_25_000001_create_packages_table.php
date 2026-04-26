<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sport_type')->default('padel');
            $table->enum('type', ['sessions', 'monthly', 'quarterly', 'yearly', 'custom']);
            $table->unsignedSmallInteger('session_count')->nullable()->comment('Number of sessions included (for sessions type)');
            $table->unsignedSmallInteger('duration_days')->nullable()->comment('Validity period in days');
            $table->decimal('price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('package_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('starts_at');
            $table->date('expires_at');
            $table->unsignedSmallInteger('sessions_remaining')->nullable()->comment('Remaining sessions for session-based packages');
            $table->enum('status', ['active', 'expired', 'suspended', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['package_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_subscriptions');
        Schema::dropIfExists('packages');
    }
};
