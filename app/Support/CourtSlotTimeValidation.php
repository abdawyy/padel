<?php

namespace App\Support;

use Illuminate\Validation\Validator;

class CourtSlotTimeValidation
{
    public static function applyToValidator(Validator $validator, string $startKey = 'start_time', string $endKey = 'end_time'): void
    {
        $validator->after(function (Validator $validator) use ($startKey, $endKey) {
            $start = (string) $validator->getData()[$startKey] ?? '';
            $end = (string) $validator->getData()[$endKey] ?? '';

            if ($start === '' || $end === '') {
                return;
            }

            if ($start === $end) {
                $validator->errors()->add($endKey, 'End time must be different from start time.');

                return;
            }

            // Same-day slots: end after start. Overnight slots: end before start (e.g. 22:00–02:00).
            // Both are valid; no further rule needed.
        });
    }
}
