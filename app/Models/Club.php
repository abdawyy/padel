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
        'sport_rules',
    ];

    protected function casts(): array
    {
        return [
            'settings'     => 'array',
            'sport_rules'  => 'array',
            'approved_at'  => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->registration_status === 'pending';
    }

    /**
     * Returns the sport-specific rules for a given sport type, merged with defaults.
     * Example structure per sport:
     * {
     *   "padel":      { "max_players": 4, "match_duration_minutes": 90 },
     *   "tennis":     { "max_players": 2, "match_duration_minutes": 60 },
     *   "pickleball": { "max_players": 4, "match_duration_minutes": 60 }
     * }
     */
    public function getRulesForSport(string $sport): array
    {
        $defaults = [
            'padel'      => ['max_players' => 4, 'match_duration_minutes' => 90, 'court_dimensions' => '20x10'],
            'tennis'     => ['max_players' => 2, 'match_duration_minutes' => 60, 'court_dimensions' => '23.77x8.23'],
            'pickleball' => ['max_players' => 4, 'match_duration_minutes' => 60, 'court_dimensions' => '13.41x6.1'],
            'squash'     => ['max_players' => 2, 'match_duration_minutes' => 45, 'court_dimensions' => '9.75x6.4'],
        ];

        $base    = $defaults[$sport] ?? ['max_players' => 2, 'match_duration_minutes' => 60];
        $custom  = ($this->sport_rules ?? [])[$sport] ?? [];

        return array_merge($base, $custom);
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
