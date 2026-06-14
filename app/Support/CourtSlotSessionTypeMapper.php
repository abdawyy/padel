<?php

namespace App\Support;

class CourtSlotSessionTypeMapper
{
    /**
     * Map court-slot slot_type values to academy session_type values.
     */
    public static function toSessionType(string $slotType): string
    {
        return match ($slotType) {
            'training' => 'group_training',
            default => $slotType,
        };
    }
}
