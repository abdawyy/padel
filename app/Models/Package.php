<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAdminClub;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, ScopedToAdminClub, SoftDeletes;

    protected $fillable = [
        'club_id',
        'name',
        'sport_type',
        'type',
        'session_count',
        'max_players',
        'duration_days',
        'price',
        'price_per_player',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'price_per_player' => 'decimal:2',
            'session_count'    => 'integer',
            'max_players'      => 'integer',
            'duration_days'    => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $package): void {
            if ($package->max_players > 0 && (float) $package->price > 0 && (float) $package->price_per_player <= 0) {
                $package->setAttribute(
                    'price_per_player',
                    sprintf('%.2f', ((float) $package->price / (int) $package->max_players))
                );
            }
        });
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(PackageSubscription::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'package_subscriptions')
            ->withPivot('starts_at', 'expires_at', 'sessions_remaining', 'status', 'notes')
            ->withTimestamps();
    }
}
