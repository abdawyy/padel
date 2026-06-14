<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clubs')->where('subscription_status', 'cancelled')->update(['subscription_status' => 'inactive']);
    }

    public function down(): void
    {
        // no-op
    }
};
