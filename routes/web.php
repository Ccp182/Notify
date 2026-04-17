<?php

use App\Http\Controllers\AppSelectController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SSOController;
use Illuminate\Support\Facades\Route;

/*
 * HMNotify - Rutas Web
 * -----------------------------------------------------------------------------
 * Estructura:
 *   - Rutas publicas: login / callback / logout.
 *   - Rutas con SSO valido pero sin app seleccionada: /select-app (Opcion B).
 *   - Rutas completamente protegidas: dashboard, wizard, CSV masivo, AJAX.
 */

// Root redirect
Route::get('/', fn() => redirect()->route('dashboard'))->name('local');

// ---- Publicas (sin auth) ----
Route::get('/login',         [SSOController::class, 'redirectToProvider'])->name('login');
Route::get('/auth/callback', [SSOController::class, 'handleCallback'])->name('sso.callback');
Route::post('/logout',       [SSOController::class, 'logout'])->name('logout');


// ---- Con SSO valido (sin necesidad de app seleccionada) ----
Route::middleware(['sso'])->group(function () {
    Route::get('/select-app',  [AppSelectController::class, 'show'])->name('select-app.show');
    Route::post('/select-app', [AppSelectController::class, 'select'])->name('select-app.select');
});


// ---- Protegidas (SSO + AppNotify en sesion) ----
Route::middleware(['sso', 'app.select'])->group(function () {

    // Dashboard y wizard
    Route::get('/dashboard', [MessageController::class, 'dashboard'])->name('dashboard');
    Route::get('/wizard',    [MessageController::class, 'notificationWizard2'])->name('wizard');

    // Envios
    Route::post('/wizard/send',              [MessageController::class, 'postDispositivosSendNew2'])->name('wizard.send');
    Route::post('/wizard/template-send',     [MessageController::class, 'postDispoTemplateSendNew'])->name('wizard.template-send');
    Route::post('/wizard/template-num-send', [MessageController::class, 'postDispoTemplateNumSendNew'])->name('wizard.template-num-send');

    // AJAX proxies al SSO
    Route::post('/api-proxy/dispositivos',     [MessageController::class, 'getDispositivos'])->name('api.dispositivos');
    Route::post('/api-proxy/dispositivos-alt', [MessageController::class, 'getDispositivosAlt'])->name('api.dispositivos-alt');
    Route::get('/api-proxy/catalogos',         [MessageController::class, 'getCatalogos'])->name('api.catalogos');
});
