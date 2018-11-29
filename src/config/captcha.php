<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'is_china' => false,
    'options' => [
        'timeout' => 30,
    ],
];
