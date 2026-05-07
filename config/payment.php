<?php

return [
    'paygate_a' => [
        'secret' => env('PAYGATE_A_SECRET', 'paygate_a_secret'),
    ],
    'paygate_b' => [
        'secret' => env('PAYGATE_B_SECRET', 'paygate_b_secret'),
    ],
];
