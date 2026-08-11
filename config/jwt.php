<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clave de firma
    |--------------------------------------------------------------------------
    |
    | Se usa tal cual (incluido el prefijo "base64:" de APP_KEY) como secreto
    | HMAC-SHA512. Cambiarla invalida todos los tokens ya emitidos.
    |
    */

    'key' => env('JWT_KEY', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Vida del token, en segundos
    |--------------------------------------------------------------------------
    |
    | 30 dias. Como no hay refresh tokens, este es el tiempo que el usuario
    | de la app movil sigue logueado sin volver a escribir la contrasena.
    |
    */

    'ttl' => 60 * 60 * 24 * 30,

    // Claims "iss" y "aud". Se validan en cada request, asi que cambiarlos
    // tambien invalida los tokens ya emitidos.
    'issuer' => env('JWT_ISSUER', env('APP_NAME', 'MyAppointments')),

    'audience' => env('JWT_AUDIENCE', ''),

];
