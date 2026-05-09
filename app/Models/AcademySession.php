<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'court_id',
        'coach_user_id',
        'created_by_user_id',
        'title',
        'sport_type',
        'session_type',
        'skill_level',
        'start_time',
        'end_time',
        'max_players',
        'price_per_player',
        'status',
        'notes',
        'session_plan',
        'video_url',
        'video_urls',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'max_players' => 'integer',
            'price_per_player' => 'decimal:2',
            'video_urls' => 'array',
        ];
    }

    public function getTrainingVideoUrlsAttribute(): array
    {
        return collect($this->video_urls ?? [])
            ->prepend($this->video_url)
            ->filter(fn ($url) => is_string($url) && filled(trim($url)))
            ->map(fn (string $url) => trim($url))
            ->unique()
            ->values()
            ->all();
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'academy_session_user')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }
}
