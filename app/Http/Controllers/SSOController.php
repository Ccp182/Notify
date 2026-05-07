<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * SSOController
 * -----------------------------------------------------------------------------
 * Cliente OAuth2 (Authorization Code) contra HMSrvAuth (auth.24hm.net).
 *
 * Reglas de oro (ver skill hm-client-sso):
 *  1. session('Pais') = pais OAuth de routing, NUNCA se sobrescribe con
 *     el Country del perfil que retorna /me.
 *  2. Siempre mandar 'country' en el body del POST /oauth/token.
 *  3. Siempre usar /api/{country}/AuthSSO/me (no /api/me).
 *  4. Un solo client_id / secret replicado en PX_DB de cada pais.
 */
class SSOController extends Controller
{
    /**
     * Redirige al /oauth/authorize de HMSrvAuth para iniciar el flujo SSO.
     */
    public function redirectToProvider(Request $request)
    {
        // Si ya hay token valido, saltar al home
        if (Session::has('access_token')) {
            return redirect()->route('local');
        }

        // Mostrar errores previos si los hay
        if ($request->session()->has('errors')) {
            return response()->view('auth.sso-error', [
                'errors' => $request->session()->get('errors'),
            ]);
        }

        $config = config('services.core_sso');
        $state  = Str::random(40);
        Session::put('oauth_state', $state);

        $query = http_build_query([
            'client_id'     => $config['client_id'],
            'redirect_uri'  => $config['redirect'],
            'response_type' => 'code',
            'scope'         => '',
            'state'         => $state,
        ]);

        return redirect()->away(
            rtrim($config['base_uri'], '/').'/oauth/authorize?'.$query
        );
    }

    /**
     * Callback desde HMSrvAuth tras autorizar. Recibe ?code=&state=&country=
     * y hace el intercambio por access_token.
     */
    public function handleCallback(Request $request)
    {
        $state = $request->query('state');
        $code  = $request->query('code');

        // Validar state para prevenir CSRF
        if (!$state || $state !== Session::get('oauth_state')) {
            abort(403, 'Estado OAuth invalido.');
        }
        Session::forget('oauth_state');

        if (!$code) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'No se recibio el codigo de autorizacion.']);
        }

        // Pais inyectado por HMSrvAuth via AppendCountryToOAuthRedirect.
        // CRITICO: este es el pais OAuth de routing, no se sobrescribe despues.
        $country = strtoupper((string) $request->query('country', 'EC'));
        Session::put('Pais', $country);

        // sso_session_id de la sesion SSO compartida (cookie .24hm.net/hm_sso).
        // Llega como query desde HMSrvAuth (AppendCountryToOAuthRedirect) y se
        // reenvia al POST /oauth/token para que el servidor pueda tagear el
        // access_token y soportar Single Logout (revocar SOLO los tokens de
        // esta sesion al hacer logout).
        $hmSso = (string) $request->query('hm_sso', '');
        if ($hmSso !== '') {
            Session::put('sso_session_id', $hmSso);
        }

        $config = config('services.core_sso');

        // Intercambiar code por access_token. OBLIGATORIO mandar country
        // en el body para que DomainMiddleware del servidor SSO seleccione
        // la BD correcta donde vive el auth_code. hm_sso permite tagear
        // el token con la sesion SSO para Single Logout.
        $tokenResponse = Http::asForm()->post(
            rtrim($config['base_uri'], '/').'/oauth/token',
            array_filter([
                'grant_type'    => 'authorization_code',
                'client_id'     => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri'  => $config['redirect'],
                'code'          => $code,
                'country'       => $country,
                'hm_sso'        => $hmSso !== '' ? $hmSso : null,
            ], fn ($v) => $v !== null)
        );

        if (!$tokenResponse->ok()) {
            Log::error('SSO: Error al obtener access_token', [
                'status' => $tokenResponse->status(),
                'body'   => $tokenResponse->body(),
            ]);
            return redirect()->route('login')
                ->withErrors(['oauth' => 'No se pudo obtener el token de acceso.']);
        }

        $tokenData    = $tokenResponse->json();
        $accessToken  = $tokenData['access_token']  ?? null;
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $expiresIn    = $tokenData['expires_in']    ?? null;

        if (!$accessToken) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Respuesta del servidor de autenticacion invalida.']);
        }

        Session::put('access_token', $accessToken);
        if ($refreshToken) Session::put('refresh_token', $refreshToken);
        if ($expiresIn)    Session::put('token_expires_at', now()->addSeconds($expiresIn));

        // Obtener perfil del usuario usando el MISMO country de routing
        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get(rtrim($config['base_uri'], '/').'/api/'.$country.'/AuthSSO/me');

        if ($userResponse->ok()) {
            $userData = $userResponse->json();
            Session::put('UCode',       $userData['Ucode']   ?? null);
            Session::put('Uid',         $userData['Uid']     ?? null);
            Session::put('FName',       $userData['FName']   ?? null);
            Session::put('User',        $userData['User']    ?? null);
            Session::put('CatCode',     $userData['CatCode'] ?? null);
            Session::put('UserCountry', $userData['Country'] ?? null);  // pais del PERFIL (informativo)
            // Mail (primer email) - usado por sso-account-menu component
            Session::put('Mail',        $userData['Mail']    ?? null);
            Session::put('user',        $userData);
            // NO HACER: Session::put('Pais', $userData['Country']);
        } else {
            Log::warning('SSO: No se pudo obtener /api/{country}/AuthSSO/me', [
                'status'  => $userResponse->status(),
                'country' => $country,
            ]);
        }

        // Cargar apps del usuario para el waffle / app launcher.
        try {
            $appsResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->get(rtrim($config['base_uri'], '/').'/api/'.$country.'/AuthSSO/apps');
            if ($appsResponse->ok()) {
                Session::put('SsoApps', $appsResponse->json('Apps') ?? []);
            } else {
                Log::warning('SSO: No se pudo cargar /AuthSSO/apps en callback', [
                    'status'  => $appsResponse->status(),
                    'body'    => substr((string) $appsResponse->body(), 0, 500),
                    'country' => $country,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('SSO: excepcion al cargar /AuthSSO/apps en callback', [
                'message' => $e->getMessage(),
                'country' => $country,
            ]);
        }

        // Tras login exitoso siempre pasamos por la seleccion de app
        return redirect()->route('select-app.show');
    }

    /**
     * Logout centralizado: invalida sesion local y redirige al /logout
     * de HMSrvAuth para cerrar la sesion SSO global.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Session::flush();

        $config       = config('services.core_sso');
        $redirectBack = route('login');

        return redirect()->away(
            rtrim($config['base_uri'], '/').'/logout?redirect_uri='.urlencode($redirectBack)
        );
    }
}
