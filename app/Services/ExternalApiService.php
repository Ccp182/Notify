<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * ExternalApiService
 * -----------------------------------------------------------------------------
 * Cliente HTTP para consumir endpoints protegidos de HMSrvAuth
 * (auth.24hm.net). Construye URLs /api/{country}/... usando el pais
 * de routing OAuth (session('Pais')) y agrega Authorization Bearer
 * con el access_token de sesion.
 *
 * Uso:
 *   $api  = app(ExternalApiService::class);
 *   $data = $api->post('Notify/getDispoByApp', ['app' => 1]);
 *   $apps = $api->post('Notify/getUserApps');
 */
class ExternalApiService
{
    // Reenvía IP + UserAgent del usuario final al SSO (bitácora de actividad).
    use \App\Support\ReenviaIdentidadCliente;

    protected function baseUri(): string
    {
        return rtrim(config('services.core_sso.base_uri', ''), '/');
    }

    protected function country(): string
    {
        return strtoupper((string) Session::get('Pais', 'EC'));
    }

    protected function client(): PendingRequest
    {
        // Todas las llamadas al SSO pasan por aquí (get/post y sus variantes
        // cacheadas), así que las cabeceras de identidad se agregan en un solo
        // punto y ninguna llamada queda sin ellas.
        return Http::withToken(Session::get('access_token'))
            ->withHeaders($this->cabecerasCliente())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->connectTimeout(10);
    }

    /**
     * Construye la URL completa /api/{country}/{path}.
     */
    protected function url(string $path): string
    {
        return $this->baseUri().'/api/'.$this->country().'/'.ltrim($path, '/');
    }

    /**
     * GET a un endpoint protegido.
     * Retorna el body JSON decoded o null si falla.
     */
    public function get(string $path, array $query = []): ?array
    {
        $url = $this->url($path);

        try {
            $res = $this->client()->get($url, $query);
        } catch (\Throwable $e) {
            Log::error('ExternalApiService GET error', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        }

        if ($res->status() === 401) {
            // Token invalido / expirado. Dejar que el middleware redirija.
            return null;
        }

        if (!$res->ok()) {
            Log::warning('ExternalApiService GET non-ok', [
                'url'    => $url,
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return null;
        }

        return $res->json();
    }

    /**
     * POST JSON a un endpoint protegido.
     * Retorna el body JSON decoded o null si falla.
     */
    public function post(string $path, array $payload = []): ?array
    {
        $url = $this->url($path);

        // Inyectar IP del cliente si el caller no la puso
        if (!isset($payload['ipClient'])) {
            $payload['ipClient'] = request()->ip();
        }

        try {
            $res = $this->client()->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('ExternalApiService POST error', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        }

        if ($res->status() === 401) {
            return null;
        }

        if (!$res->ok()) {
            Log::warning('ExternalApiService POST non-ok', [
                'url'    => $url,
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return null;
        }

        return $res->json();
    }

    /**
     * POST Notify/acceso: ¿este token puede usar Notify? (pantalla de subusuarios).
     * A diferencia de post(), NO traga las excepciones de red: las deja subir para
     * que AccesoNotify decida (ante un fallo deja pasar; la barrera real es el 403
     * del SSO). Usa el mismo client() (token de sesion + cabeceras de identidad) y
     * un timeout corto de 10 s. Retorna el JSON decodificado o null si la respuesta
     * no fue 200.
     */
    public function acceso(): ?array
    {
        $res = $this->client()
            ->timeout(10)
            ->post($this->url('Notify/acceso'), [
                'origen'   => 'pantalla',
                'ipClient' => request()->ip(),
            ]);

        return $res->ok() ? $res->json() : null;
    }

    /**
     * GET con cache por pais + path + query.
     * Util para catalogos estables (TipoEntidad, Plataformas).
     */
    public function cachedGet(string $path, array $query = [], int $ttl = 60): ?array
    {
        $key = 'sso_api:'.$this->country().':'.md5($path.serialize($query));
        if (($hit = Cache::get($key)) !== null) return $hit;
        $fresh = $this->get($path, $query);
        if ($this->isCacheable($fresh)) Cache::put($key, $fresh, $ttl);
        return $fresh;
    }

    /**
     * POST con cache por pais + path + payload.
     * Util para catalogos del wizard (TipoEntidad, GrupoSubUsuario, Plataformas)
     * que cambian poco por sesion.
     */
    public function cachedPost(string $path, array $payload = [], int $ttl = 60): ?array
    {
        $key = 'sso_api:'.$this->country().':post:'.md5($path.serialize($payload));
        if (($hit = Cache::get($key)) !== null) return $hit;
        $fresh = $this->post($path, $payload);
        if ($this->isCacheable($fresh)) Cache::put($key, $fresh, $ttl);
        return $fresh;
    }

    /**
     * No cacheamos respuestas vacias/erroneas: evita dejar en cache por 30min
     * un fallo transitorio (p.ej. catalogo de TipoEntidad vacio).
     */
    private function isCacheable($resp): bool
    {
        if (!is_array($resp) || empty($resp)) return false;
        if (array_key_exists('Error', $resp) && $resp['Error']) return false;
        return true;
    }
}
