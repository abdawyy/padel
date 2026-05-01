<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubSaasSubscription extends Model
{
    protected $fillable = [
        'club_id',
        'saas_plan_id',
        'billing_cycle',
        'amount_paid',
        'starts_at',
        'ends_at',
        'status',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'starts_at'   => 'date',
            'ends_at'     => 'date',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->ends_at, false));
    }

    /**
     * Sync the parent club's subscription_status based on this subscription.
     */
    public function syncClubStatus(): void
    {
        $status = match (true) {
            $this->status === 'trial'     => 'trial',
            $this->status === 'past_due'  => 'active',   // grace period — still active
            $this->status === 'cancelled' => 'inactive',
            $this->isExpired()            => 'inactive',
            $this->isActive()             => 'active',
            default                       => 'inactive',
        };

        $this->club()->update(['subscription_status' => $status]);
    }
}
