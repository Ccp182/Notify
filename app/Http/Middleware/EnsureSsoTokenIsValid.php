<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSsoTokenIsValid
 * -----------------------------------------------------------------------------
 * Middleware que protege las rutas autenticadas. Verifica:
 *   1. Que exista access_token en sesion y no haya expirado (por TTL local).
 *   2. Validacion periodica contra /api/{country}/AuthSSO/me (cada 60s
 *      en GETs no-Ajax) para detectar invalidaciones remotas del token.
 *
 * Regla clave: NUNCA sobrescribir session('Pais') con $userData['Country'].
 */
class EnsureSsoTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = Session::get('access_token');
        $expiresAt   = Session::get('token_expires_at');

        if (!$accessToken || ($expiresAt && now()->greaterThan($expiresAt))) {
            $this->clearSession($request);
            return $this->bounceToLogin($request);
        }

        // Single Logout via cookie compartida .24hm.net/hm_sso.
        // Si el sso_session_id de la cookie difiere del que guardamos al
        // login, significa que el usuario cerro sesion en otro tab/app
        // (o expiro la sesion central). Forzamos logout local de inmediato.
        $cookieSso = $request->cookie('hm_sso');
        $storedSso = Session::get('sso_session_id');
        if ($storedSso) {
            if (!$cookieSso || $cookieSso !== $storedSso) {
                $this->clearSession($request);
                return $this->bounceToLogin($request);
            }
        }

        // Validar contra /me solo en GETs no-Ajax, con cache de 60s
        if ($request->isMethod('GET') && !$request->ajax() && !$request->expectsJson()) {
            $lastCheck = Session::get('sso_last_check');

            if (!$lastCheck || now()->diffInSeconds($lastCheck) >= 60) {
                $config  = config('services.core_sso');
                $country = strtoupper((string) Session::get('Pais', 'EC'));

                try {
                    $response = Http::withToken($accessToken)
                        ->acceptJson()
                        ->withoutRedirecting()
                        ->timeout(10)
                        ->get(rtrim($config['base_uri'], '/').'/api/'.$country.'/AuthSSO/me');
                } catch (\Throwable $e) {
                    Log::warning('SSO: excepcion al validar token contra /me', [
                        'message' => $e->getMessage(),
                    ]);
                    // En error de red no cerramos sesion: el token local aun es valido.
                    return $this->shareViewData($next($request));
                }

                if (!$response->ok()) {
                    Log::warning('SSO: token invalido en validacion periodica', [
                        'status'  => $response->status(),
                        'country' => $country,
                    ]);
                    $this->clearSession($request);
                    return $this->bounceToLogin($request);
                }

                $userData = $response->json();
                Session::put('UCode',       $userData['Ucode']   ?? Session::get('UCode'));
                Session::put('Uid',         $userData['Uid']     ?? Session::get('Uid'));
                Session::put('FName',       $userData['FName']   ?? Session::get('FName'));
                Session::put('User',        $userData['User']    ?? Session::get('User'));
                Session::put('UserCountry', $userData['Country'] ?? Session::get('UserCountry'));
                // Mail (primer email) - usado por sso-account-menu component
                Session::put('Mail',        $userData['Mail']    ?? Session::get('Mail'));
                Session::put('user',        $userData);
                Session::put('sso_last_check', now());
                // NO HACER: Session::put('Pais', $userData['Country']);

                // Refrescar lista de apps del usuario (waffle / app launcher).
                try {
                    $appsResponse = Http::withToken($accessToken)
                        ->acceptJson()
                        ->withoutRedirecting()
                        ->timeout(10)
                        ->get(rtrim($config['base_uri'], '/').'/api/'.$country.'/AuthSSO/apps');
                    if ($appsResponse->ok()) {
                        Session::put('SsoApps', $appsResponse->json('Apps') ?? []);
                    } else {
                        Log::warning('SSO: /AuthSSO/apps respondio con error en revalidacion', [
                            'status'  => $appsResponse->status(),
                            'body'    => substr((string) $appsResponse->body(), 0, 500),
                            'country' => $country,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('SSO: excepcion al refrescar /AuthSSO/apps', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $this->shareViewData($next($request));
    }

    /**
     * Comparte datos de sesion con todas las vistas para que los
     * layouts (topbar, sidebar) los muestren sin queries redundantes.
     */
    private function shareViewData(Response $response): Response
    {
        view()->share('FName',      Session::get('FName'));
        view()->share('User',       Session::get('User'));
        view()->share('Mail',       Session::get('Mail'));
        view()->share('Pais',       Session::get('Pais'));
        view()->share('AppNotify',  Session::get('AppNotify'));
        return $response;
    }

    private function clearSession(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Session::flush();
    }

    /**
     * Redirect al login. Para peticiones Ajax/JSON devuelve 401 con la
     * URL de login para que el interceptor del frontend redirija el
     * navegador. Esto evita ver "datos rotos" tras un Single Logout.
     */
    private function bounceToLogin(Request $request): Response
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok'        => false,
                'message'   => 'Sesion expirada',
                'login_url' => route('login'),
            ], 401);
        }
        return redirect()->route('login');
    }
}
