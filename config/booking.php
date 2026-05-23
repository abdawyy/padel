<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pending payment hold (minutes)
    |--------------------------------------------------------------------------
    |
    | Open-match joiners with payment_status=pending count toward capacity
    | only within this window. Older pending rows are removed by the scheduler.
    |
    */
    'pending_payment_hold_minutes' => (int) env('BOOKING_PENDING_PAYMENT_HOLD_MINUTES', 30),

];
