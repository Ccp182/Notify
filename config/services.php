<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HMSrvAuth (Core SSO)
    |--------------------------------------------------------------------------
    |
    | Configuracion del cliente OAuth2 contra auth.24hm.net (HMSrvAuth).
    | Las credenciales vienen del .env para no commitear secretos.
    |
    | base_uri:      URL base del servidor SSO (sin slash final).
    | client_id:     UUID del cliente OAuth generado con `passport:client`.
    | client_secret: secret plaintext (se usa solo en body de /oauth/token).
    | redirect:      callback publico de HMNotify que recibe el auth code.
    |
    */

    'core_sso' => [
        'base_uri'      => env('CORE_SSO_BASE_URI', 'https://auth.24hm.net'),
        'client_id'     => env('CORE_SSO_CLIENT_ID'),
        'client_secret' => env('CORE_SSO_CLIENT_SECRET'),
        'redirect'      => env('CORE_SSO_REDIRECT_URI'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
