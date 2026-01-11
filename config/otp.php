<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Expiry Time (in minutes)
    |--------------------------------------------------------------------------
    |
    | This value determines how long an OTP will remain valid after it has
    | been generated. After this time period, the OTP will expire and a
    | new one will need to be requested.
    |
    */

    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | This value determines the number of digits in the OTP code.
    | Default is 6 digits.
    |
    */

    'length' => env('OTP_LENGTH', 6),

];
