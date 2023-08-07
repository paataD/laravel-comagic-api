<?php
/*
|--------------------------------------------------------------------------
| Laravel Comagic.ru API config
|--------------------------------------------------------------------------
*/
return [
    'debug' => env('COMAGIC_DEBUG', false),

    'host' => env('COMAGIC_HOST', 'https://dataapi.comagic.ru/'),
    'api_v' => env('COMAGIC_API_VER', 'v2.0'),

    'login' => env('COMAGIC_LOGIN'),
    'password' => env('COMAGIC_PASSWORD'),

    // required for Call API if login and password not specified
    'access_token' => env('COMAGIC_TOKEN'),
];