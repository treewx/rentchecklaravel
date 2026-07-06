<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Late Payment Cutoff
    |--------------------------------------------------------------------------
    |
    | Bank payments dated on the due date arrive via overnight batches and
    | Akahu needs time to sync them, so rent due on day D is not considered
    | late until the following morning. This is the hour (0-23, NZ time)
    | on the day AFTER the due date at which unpaid rent becomes "late".
    | All times use the application timezone (Pacific/Auckland), so the
    | hosting region has no effect.
    |
    */

    'late_cutoff_hour' => (int) env('RENT_LATE_CUTOFF_HOUR', 8),

];
