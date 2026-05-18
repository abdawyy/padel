<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Package::query()
            ->where('price_per_player', '<=', 0)
            ->where('max_players', '>', 0)
            ->get()
            ->each(function (Package $package): void {
                $package->price_per_player = number_format(((float) $package->price / (int) $package->max_players), 2, '.', '');
                    $package->saveQuietly();
            });
    }

    public function down(): void
    {
        Package::query()
            ->where('max_players', '>', 0)
            ->update(['price_per_player' => 0]);
    }
};