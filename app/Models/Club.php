<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sport_type',
        'address',
        'subscription_status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function saasSubscriptions()
    {
        return $this->hasMany(ClubSaasSubscription::class);
    }

    public function activeSaasSubscription()
    {
        return $this->hasOne(ClubSaasSubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->latestOfMany();
    }

    public function latestSaasSubscription()
    {
        return $this->hasOne(ClubSaasSubscription::class)->latestOfMany();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'club_users')->withPivot('role')->withTimestamps();
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    public function courtSlots()
    {
        return $this->hasManyThrough(CourtSlot::class, Court::class);
    }

    public function academySessions()
    {
        return $this->hasMany(AcademySession::class);
    }
}
