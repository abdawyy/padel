<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'court_id',
        'sport_type',
        'owner_user_id',
        'coach_user_id',
        'start_time',
        'end_time',
        'total_price',
        'coach_fee',
        'match_type',
        'session_type',
        'max_players',
        'skill_level',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'total_price' => 'decimal:2',
            'coach_fee' => 'decimal:2',
            'max_players' => 'integer',
        ];
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'booking_participants')->withPivot('amount_due', 'payment_status');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
