<?php

namespace Tests\Unit;

use App\Services\BookingPaymentSplit;
use PHPUnit\Framework\TestCase;

class BookingPaymentSplitTest extends TestCase
{
    public function test_split_amounts_sum_to_total(): void
    {
        $amounts = BookingPaymentSplit::split(100.00, 4);

        $this->assertCount(4, $amounts);
        $this->assertEqualsWithDelta(100.00, array_sum($amounts), 0.001);
    }

    public function test_remainder_goes_to_first_participant(): void
    {
        $amounts = BookingPaymentSplit::split(100.00, 3);

        $this->assertEqualsWithDelta(100.00, array_sum($amounts), 0.001);
        $this->assertGreaterThan($amounts[1], $amounts[0]);
    }

    public function test_amount_for_slot_matches_split_array(): void
    {
        $total = 75.50;
        $max = 4;

        for ($slot = 0; $slot < $max; $slot++) {
            $this->assertSame(
                BookingPaymentSplit::split($total, $max)[$slot],
                BookingPaymentSplit::amountForSlot($total, $max, $slot),
            );
        }
    }
}
