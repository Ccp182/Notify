<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias para uso en grupos o rutas individuales
        $middleware->alias([
            'sso'         => \App\Http\Middleware\EnsureSsoTokenIsValid::class,
            'app.select'  => \App\Http\Middleware\EnsureAppSelected::class,
        ]);

        // Single Logout: la cookie hm_sso es compartida en .24hm.net y la
        // setea HMSrvAuth con su APP_KEY. Si la encriptamos aqui con nuestro
        // APP_KEY rompemos el flujo. Se mantiene como plaintext (UUID opaco).
        $middleware->encryptCookies(except: [
            'hm_sso',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
