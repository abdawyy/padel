<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->text('message')->nullable();          // coach's note when applying
            $table->text('response_note')->nullable();    // manager's response
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['academy_session_id', 'coach_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_applications');
    }
};
