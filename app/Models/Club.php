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
        'registration_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings'    => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->registration_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->registration_status === 'approved';
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
