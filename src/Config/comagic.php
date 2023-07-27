<?php
/*
|--------------------------------------------------------------------------
| Laravel Comagic.ru API config
|--------------------------------------------------------------------------
*/
return [
    'login' => env('COMAGIC_LOGIN'),
    'password' => env('COMAGIC_PASSWORD'),
    // required for Call API if login and password not specified
    'access_token' => env('COMAGIC_TOKEN'),
];