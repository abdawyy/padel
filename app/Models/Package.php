<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'name',
        'sport_type',
        'type',
        'session_count',
        'duration_days',
        'price',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'session_count'    => 'integer',
            'duration_days'    => 'integer',
            'is_active'        => 'boolean',
        ];
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
