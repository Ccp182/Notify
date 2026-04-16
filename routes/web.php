<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/Message/'.Session::get('Pais').'/'.Session::get('AppNAME').'/create');
    } else {
        return redirect()->route('login');
    }
});

Route::middleware(['guest'])->group(function () {
    Route::get('login', LoginController::class)->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('loginex');
});
Route::middleware(['web'])->group(function () {
    //Route::post('login_ext', [LoginController::class, 'loginextpost'])->name('loginextpost');
});

Route::middleware(['auth'])->group(function () {
    Route::get('mapa',  [MessageController::class,'create'])->name('mapa');
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('getDispositivos', [MessageController::class, 'getDispositivos'])->name('getDispositivos');
    Route::post('getDispositivosAlt', [MessageController::class, 'getDispositivosAlt'])->name('getDispositivosAlt');
    Route::post('postDispositivosSend', [MessageController::class, 'postDispositivosSend'])->name('postDispositivosSend');
    Route::post('postDispositivosSendNew', [MessageController::class, 'postDispositivosSendNew'])->name('postDispositivosSendNew');
    Route::post('postDispoTemplateSendNew', [MessageController::class, 'postDispoTemplateSendNew'])->name('postDispoTemplateSendNew');
    
    Route::post('postDispositivosSendNew2', [MessageController::class, 'postDispositivosSendNew2'])->name('postDispositivosSendNew2');
    Route::post('postDispoTemplateNumSendNew', [MessageController::class, 'postDispoTemplateNumSendNew'])->name('postDispoTemplateNumSendNew');

    Route::group(['prefix' => 'Message/{country}/{app_name}', 'middleware' => 'domain'],function (){
        Route::get('/create', [MessageController::class,'create']);
        Route::get('/notification', [MessageController::class,'notificationWizard']);
        Route::get('/notification2', [MessageController::class,'notificationWizard2']);
       
    });
    
});

/**$router->group(['prefix' => 'Message/{country}/{app_name}', 'middleware' => 'domain'],function () use ($router) {
    $router->get('/create', [MessageController::class,'create']);
    
   
});**/

