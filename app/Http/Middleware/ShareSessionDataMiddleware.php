<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ShareSessionDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    /*public function handle(Request $request, Closure $next)
    {
        if($request->session()->has('UserHM') && $request->session()->has('PassHM') && $request->session()->has('AppHM')){
            $response = Http::post(config('global.URL_HM').'AM/'.$request->session()->get('Pais').'/verifyAuth', [
                'usuario' => $request->session()->get('UserHM'),
                'clave' => decryptAES($request->session()->get('PassHM')),
                'idAplicacion' => $request->session()->get('AppHM'),
                'ip' => getIPAddress(),
            ]);
            if(isset($response['Error']) && $response['Error']){
                if(isset($response['CodeError'])){
                    $request->session()->flush();
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    Cache::flush();
                    Session::flush();
                    config()->set('UserHM', null);
                    config()->set('PassHM', null);
                    if ($response['CodeError'] == -12 || $response['CodeError'] == -6 || $response['CodeError'] == -7) {
                        return redirect()->route('login')->with([
                            'ErrorRedirectLink' => $response['Datos']['Link'], 
                            'ErrorTitle' => $response['Title'],
                            'ErrorText' => isset($response['Mensaje'])?$response['Mensaje']:'Ha ocurrido un error, por favor intente iniciar sesión más tarde',
                            'ErrorIcon' => $response['CodeError'] == -90?'warning':'error',
                        ]);
                    }
                    //return redirect()->back()->withErrors(['username' => $e->errorMessage()]); 

                    $mensaje=$response['CodeError']==-2?'Su usuario no tiene una sesión activa o ha ocurrido un cambio de clave . Por lo cual deberá iniciar sesión nuevamente.':((isset($response['Mensaje'])?$response['Mensaje']:'Ha ocurrido un error, por favor intente iniciar sesión más tarde'));
                    return redirect()->route('login')->withErrors(['username' => $mensaje]);
                }
            }
        }
        view()->share('FName', $request->session()->get('FName'));
        view()->share('Pais', $request->session()->get('Pais'));
        return $next($request);
    }*/


    public function handle(Request $request, Closure $next)
    {
        // Comparte datos a las vistas (esto sí, siempre)
        view()->share('FName', $request->session()->get('FName'));
        view()->share('Pais',  $request->session()->get('Pais'));

        // Sin credenciales en sesión => continuar
        if (!$request->session()->has('UserGF')
            || !$request->session()->has('PassGF')
            || !$request->session()->has('AppGF')) {
            return $next($request);
        }

        // 1) Solo verifica en navegación GET (no en POST/Ajax)
        if (!$request->isMethod('get') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        // 2) TTL: evita múltiples llamadas seguidas
        $ttlSeconds = 300; // 5 minutos
        $last = $request->session()->get('lastAuthCheckAt');
        if ($last && now()->diffInSeconds($last) < $ttlSeconds) {
            return $next($request);
        }

        // 3) Verificación remota
        $response = Http::post(config('global.URL_FLEET').'AM/'.$request->session()->get('Pais').'/verifyAuth', [
            'usuario'       => $request->session()->get('UserGF'),
            'clave'         => decryptAES($request->session()->get('PassGF')),
            'idAplicacion'  => $request->session()->get('AppGF'),
            'ip' => getIPAddress(),
        ]);

        // 4) Manejo de errores
        if (data_get($response, 'Error') && data_get($response, 'CodeError') !== null) {
            // Limpia sesión y redirige como ya lo hacías
            $code = data_get($response, 'CodeError');
            $msg  = $code == -2
                ? 'Su usuario no tiene una sesión activa o ha ocurrido un cambio de clave . Por lo cual deberá iniciar sesión nuevamente.'
                : (data_get($response, 'Mensaje') ?? 'Ha ocurrido un error, por favor intente iniciar sesión más tarde');

            $request->session()->flush();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Cache::flush();
            Session::flush();

            if (in_array($code, [-12, -6, -7])) {
                return redirect()->route('login')->with([
                    'ErrorRedirectLink' => data_get($response, 'Datos.Link'),
                    'ErrorTitle'        => data_get($response, 'Title'),
                    'ErrorText'         => data_get($response, 'Mensaje') ?? $msg,
                    'ErrorIcon'         => $code == -90 ? 'warning' : 'error',
                ]);
            }
            return redirect()->route('login')->withErrors(['username' => $msg]);
        }

        // 5) Marca verificado
        $request->session()->put('lastAuthCheckAt', now());

        return $next($request);
    }
}
