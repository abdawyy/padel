<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'title',
        'sport_type',
        'slot_type',
        'day_of_week',
        'start_time',
        'end_time',
        'coach_user_id',
        'max_players',
        'price',
        'skill_level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'max_players' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }
}
