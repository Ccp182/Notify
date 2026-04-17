<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        // Disco especifico para imagenes/HTML del wizard v2.
        // Apunta a public/assets/upload, que es servido directamente
        // por el webserver (sin pasar por Laravel).
        'uploads' => [
            'driver'     => 'local',
            'root'       => public_path('assets/upload'),
            'url'        => env('APP_URL').'/assets/upload',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
