<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
    // Reenvía IP + UserAgent del usuario final al SSO: estas llamadas (/me y
    // /apps) también quedan en la bitácora y sin esto registrarían la IP del
    // servidor y "GuzzleHttp/7".
    use \App\Support\ReenviaIdentidadCliente;

    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = Session::get('access_token');
        $expiresAt   = Session::get('token_expires_at');

        if ($accessToken) {
            // AU14 inactividad: mas de 120 min sin peticiones -> se cierra la sesion. El tope
            // absoluto de 12 h lo sigue imponiendo la cookie compartida hm_sso (mas abajo).
            $ultimaActividad = Session::get('sso_ultima_actividad');
            if ($ultimaActividad && abs(Carbon::parse($ultimaActividad)->diffInMinutes(now())) > 120) {
                $this->clearSession($request);
                return $this->bounceToLogin($request);
            }
            Session::put('sso_ultima_actividad', now());

            // AU14 renovacion: a 5 min (o menos) de vencer se pide un access token nuevo con el
            // refresh_token. Si falla se cierra la sesion igual que con el token vencido.
            if ($expiresAt && Session::get('refresh_token')
                && Carbon::parse($expiresAt)->lessThanOrEqualTo(now()->addMinutes(5))) {
                if (!$this->renovarToken()) {
                    $this->clearSession($request);
                    return $this->bounceToLogin($request);
                }
                $accessToken = Session::get('access_token');
                $expiresAt   = Session::get('token_expires_at');
            }
        }

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

            // abs(): en Carbon 3 now()->diffInSeconds($pasado) es NEGATIVO y la revalidacion
            // no volvia a correr nunca (mismo fix que HMMantenedores y HMAnubis, 2026-10-17).
            if (!$lastCheck || abs(now()->diffInSeconds($lastCheck)) >= 60) {
                $config  = config('services.core_sso');
                $country = strtoupper((string) Session::get('Pais', 'EC'));

                try {
                    $response = Http::withToken($accessToken)
                        ->withHeaders($this->cabecerasCliente())
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
                        ->withHeaders($this->cabecerasCliente())
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

    /**
     * AU14: renueva el access token (grant refresh_token) contra /oauth/token del SSO,
     * con el pais de la sesion. Devuelve false ante cualquier falla; nunca registra tokens.
     */
    private function renovarToken(): bool
    {
        $config = config('services.core_sso');

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withHeaders($this->cabecerasCliente())
                ->timeout(10)
                ->post(rtrim($config['base_uri'], '/').'/oauth/token', [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => Session::get('refresh_token'),
                    'client_id'     => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'scope'         => '',
                    'country'       => strtoupper((string) Session::get('Pais', 'EC')),
                ]);
        } catch (\Throwable $e) {
            Log::warning('SSO: excepcion al renovar el access token', ['message' => $e->getMessage()]);
            return false;
        }

        $data = $response->json();
        if (!$response->ok() || empty($data['access_token'])) {
            Log::warning('SSO: no se pudo renovar el access token', ['status' => $response->status()]);
            return false;
        }

        Session::put('access_token', $data['access_token']);
        if (!empty($data['refresh_token'])) Session::put('refresh_token', $data['refresh_token']);
        if (!empty($data['expires_in'])) {
            Session::put('token_expires_at', now()->addSeconds((int) $data['expires_in']));
        } else {
            Session::forget('token_expires_at');
        }
        // La huella de AccesoNotify es sha256(access_token): con el token nuevo se re-consulta.
        Session::forget('notify_acceso');

        return true;
    }

    private function clearSession(Request $request): void
    {
        // Solo se destruye una sesion que todavia tiene token. Invalidar una ya cerrada crea
        // sesion y cookie nuevas: una pestana vieja que sigue pidiendo datos con 401 borraba
        // asi el state del login en curso de otra pestana ("sesion de inicio expiro", 2026-10-18).
        if (!Session::has('access_token')) {
            return;
        }
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
