<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'version' => env('NOCAPTCHA_VERSION'),
    'options' => [
        'timeout' => 30,
    ],
];
