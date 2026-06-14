<?php

namespace Tests\Unit;

use App\Support\CourtSlotTimeValidation;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CourtSlotTimeValidationTest extends TestCase
{
    public function test_overnight_slot_times_are_valid(): void
    {
        $validator = Validator::make([
            'start_time' => '22:00',
            'end_time' => '02:00',
        ], [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        CourtSlotTimeValidation::applyToValidator($validator);

        $this->assertFalse($validator->fails());
    }

    public function test_same_start_and_end_time_is_invalid(): void
    {
        $validator = Validator::make([
            'start_time' => '10:00',
            'end_time' => '10:00',
        ], [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        CourtSlotTimeValidation::applyToValidator($validator);

        $this->assertTrue($validator->fails());
    }
}
