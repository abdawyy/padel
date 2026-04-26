<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageSubscription extends Model
{
    protected $fillable = [
        'package_id',
        'user_id',
        'starts_at',
        'expires_at',
        'sessions_remaining',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'          => 'date',
            'expires_at'         => 'date',
            'sessions_remaining' => 'integer',
        ];
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
