<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'paymob_transaction_id',
        'amount',
        'status',
        'provider_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_payload' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
