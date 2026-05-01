<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->json('features')->nullable(); // max_courts, max_users, max_bookings_per_month etc.
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('club_saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saas_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status')->default('active'); // trial, active, past_due, cancelled, expired
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_saas_subscriptions');
        Schema::dropIfExists('saas_plans');
    }
};
