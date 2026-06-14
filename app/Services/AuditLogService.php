<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(
        string $eventType,
        ?User $actor = null,
        ?Model $subject = null,
        array $payload = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'event_type' => $eventType,
            'actor_user_id' => $actor?->id,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'payload' => $payload,
        ]);
    }
}
