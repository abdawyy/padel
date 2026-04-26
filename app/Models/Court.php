<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Court extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'sport_type',
        'name',
        'type',
        'price_per_hour',
        'capacity',
        'slot_duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'capacity' => 'integer',
            'slot_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function slots()
    {
        return $this->hasMany(CourtSlot::class);
    }

    public function academySessions()
    {
        return $this->hasMany(AcademySession::class);
    }
}
