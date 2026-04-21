<?php

use App\Http\Controllers\AppSelectController;
use App\Http\Controllers\CampainController;
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

    // Campanas (listado por usuario + KPIs dashboard)
    Route::get('/campains',            [CampainController::class, 'index'])->name('campains.index');
    Route::post('/campains/list',      [CampainController::class, 'list'])->name('campains.list');
    Route::post('/campains/kpis',      [CampainController::class, 'kpis'])->name('campains.kpis');

    // Dashboard HMNotify (resumen + serie diaria + top campanas)
    Route::post('/dashboard/resumen',  [CampainController::class, 'dashboardResumen'])->name('dashboard.resumen');
    Route::post('/dashboard/serie',    [CampainController::class, 'dashboardSerie'])->name('dashboard.serie');
    Route::post('/dashboard/top',      [CampainController::class, 'dashboardTop'])->name('dashboard.top');
    Route::post('/campains/metricas',  [CampainController::class, 'metricas'])->name('campains.metricas');
    Route::post('/campains/detalle',   [CampainController::class, 'detalle'])->name('campains.detalle');
});



Route::get('/debug/token', function () {
    dd([
        'access_token' => session('access_token'),
        'Pais'         => session('Pais'),
        'FName'        => session('FName'),
        'UCode'        => session('UCode'),
        'url_me'       => rtrim(config('services.core_sso.base_uri'), '/')
                          .'/api/'.session('Pais', 'EC').'/AuthSSO/me',
    ]);
})->middleware(\App\Http\Middleware\EnsureSsoTokenIsValid::class);