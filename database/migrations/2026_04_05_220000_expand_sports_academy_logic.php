<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('sport_type')->default('padel')->after('name');
            $table->json('settings')->nullable()->after('subscription_status');
        });

        Schema::table('courts', function (Blueprint $table) {
            $table->string('sport_type')->default('padel')->after('club_id');
            $table->unsignedSmallInteger('capacity')->default(4)->after('price_per_hour');
            $table->unsignedInteger('slot_duration_minutes')->default(60)->after('capacity');
            $table->boolean('is_active')->default(true)->after('slot_duration_minutes');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('sport_type')->default('padel')->after('court_id');
            $table->foreignId('coach_user_id')->nullable()->after('owner_user_id')->constrained('users')->nullOnDelete();
            $table->string('session_type')->default('standard')->after('match_type');
            $table->unsignedSmallInteger('max_players')->default(4)->after('session_type');
            $table->string('skill_level')->nullable()->after('max_players');
            $table->decimal('coach_fee', 8, 2)->default(0)->after('total_price');
            $table->text('notes')->nullable()->after('status');
        });

        Schema::create('court_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('sport_type')->default('padel');
            $table->string('slot_type')->default('training');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('coach_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('max_players')->default(4);
            $table->decimal('price', 8, 2)->default(0);
            $table->string('skill_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('sport_type')->default('padel');
            $table->string('session_type')->default('group_training');
            $table->string('skill_level')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedSmallInteger('max_players')->default(4);
            $table->decimal('price_per_player', 8, 2)->default(0);
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'start_time']);
            $table->index(['court_id', 'start_time']);
        });

        Schema::create('academy_session_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['academy_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academy_session_user');
        Schema::dropIfExists('academy_sessions');
        Schema::dropIfExists('court_slots');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['coach_user_id']);
            $table->dropColumn([
                'sport_type',
                'coach_user_id',
                'session_type',
                'max_players',
                'skill_level',
                'coach_fee',
                'notes',
            ]);
        });

        Schema::table('courts', function (Blueprint $table) {
            $table->dropColumn([
                'sport_type',
                'capacity',
                'slot_duration_minutes',
                'is_active',
            ]);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['sport_type', 'settings']);
        });
    }
};
