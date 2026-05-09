<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'skill_level',
        'date_of_birth',
        'preferred_sport',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'skill_level'       => 'integer',
            'date_of_birth'     => 'date',
        ];
    }

    public static function skillLevelLabel(int $level): string
    {
        return match ($level) {
            1 => 'Level 1 – Beginner',
            2 => 'Level 2 – Beginner+',
            3 => 'Level 3 – Intermediate',
            4 => 'Level 4 – Intermediate+',
            5 => 'Level 5 – Advanced',
            6 => 'Level 6 – Advanced+',
            7 => 'Level 7 – Elite / Pro',
            default => "Level {$level}",
        };
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'player') {
            return $this->is_active && $this->role === 'player';
        }

        if ($panel->getId() === 'coach') {
            return $this->is_active && $this->role === 'coach';
        }

        if ($panel->getId() === 'saas') {
            return $this->is_active && $this->role === 'super_admin';
        }

        // academy portal (admin panel) — super_admin uses /saas portal instead
        if ($panel->getId() === 'admin') {
            return $this->is_active
                && $this->role !== 'super_admin'
                && $this->hasAdminAccess();
        }

        return $this->hasAdminAccess();
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_active && $this->role === 'super_admin';
    }

    public function accessibleClubIds(): array
    {
        if (! $this->is_active) {
            return [];
        }

        if ($this->isSuperAdmin()) {
            return Club::query()->pluck('clubs.id')->map(fn ($id) => (int) $id)->all();
        }

        return $this->clubs()
            ->pluck('clubs.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function belongsToClub(int|Club|null $club = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $clubId = $club instanceof Club ? $club->getKey() : $club;

        $clubsQuery = $this->clubs();

        if ($clubId !== null) {
            $clubsQuery->where('clubs.id', $clubId);
        }

        return $clubsQuery->exists();
    }

    public function canManageClub(int|Club|null $club = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $clubId = $club instanceof Club ? $club->getKey() : $club;

        $clubsQuery = $this->clubs()->wherePivotIn('role', ['owner', 'manager']);

        if ($clubId !== null) {
            $clubsQuery->where('clubs.id', $clubId);
        }

        return $clubsQuery->exists();
    }

    public function hasAdminAccess(?Club $club = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $clubsQuery = $this->clubs();

        if ($club !== null) {
            $clubsQuery->where('clubs.id', $club->id);
        }

        if (! $clubsQuery->exists()) {
            return false;
        }

        return in_array($this->role, ['admin', 'manager', 'coach', 'staff'], true)
            || $this->canManageClub($club ?? null);
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_users')->withPivot('role')->withTimestamps();
    }

    public function ownedBookings()
    {
        return $this->hasMany(Booking::class, 'owner_user_id');
    }

    public function participatedBookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_participants')->withPivot('amount_due', 'payment_status');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function coachedBookings()
    {
        return $this->hasMany(Booking::class, 'coach_user_id');
    }

    public function academySessions()
    {
        return $this->belongsToMany(AcademySession::class, 'academy_session_user')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function coachedAcademySessions()
    {
        return $this->hasMany(AcademySession::class, 'coach_user_id');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_subscriptions')
            ->withPivot('starts_at', 'expires_at', 'sessions_remaining', 'status', 'notes')
            ->withTimestamps();
    }
}
