<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachApplication extends Model
{
    protected $fillable = [
        'academy_session_id',
        'coach_user_id',
        'status',
        'message',
        'response_note',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademySession::class, 'academy_session_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
