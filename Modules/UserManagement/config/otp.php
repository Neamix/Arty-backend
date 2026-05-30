<?php

return [
    'length' => env('OTP_LENGTH', 6),
    'expires_in_minutes' => env('OTP_EXPIRES_MINUTES', 10),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),
    'rate_limit' => [
        'max_requests' => env('OTP_RATE_LIMIT_MAX', 5),
        'decay_minutes' => env('OTP_RATE_LIMIT_DECAY', 15),
    ],
    'reset_window_minutes' => env('OTP_RESET_WINDOW_MINUTES', 15),
];
