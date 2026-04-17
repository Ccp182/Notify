<?php

/*
 * HMNotify es cliente SSO puro: la autenticacion la resuelve HMSrvAuth
 * via OAuth2/Passport, y la sesion local solo guarda el access_token
 * + datos del usuario retornados por /api/{country}/AuthSSO/me.
 *
 * No usamos Auth::attempt() ni providers custom. Este archivo existe
 * porque Laravel lo requiere, pero los guards/providers quedan con
 * valores default que NUNCA se invocan.
 */

return [

    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
