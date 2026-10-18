<?php

namespace App\Http\Middleware;

use App\Services\ExternalApiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * AccesoNotify - ¿este usuario puede usar Notify?
 *
 * Notify es SOLO para el usuario principal de la cuenta, igual que Gestor
 * de Flotas y Seshat. Pregunta a HMSrvAuth (POST Notify/acceso) y, si el
 * token es de un subusuario (motivo SUBUSUARIO_NO_SOPORTADO), muestra la pantalla
 * "Ingresa con el usuario principal" en vez de la aplicacion.
 *
 * ESTA PANTALLA ES COMODIDAD, NO CONTROL. La barrera real esta en el SSO: con el
 * token de un subusuario todas las rutas /Notify/* responden 403. Aqui solo
 * se le explica al usuario por que no entra.
 *
 * Va DESPUES de EnsureSsoTokenIsValid: necesita access_token en la sesion.
 *
 * La respuesta se guarda en session('notify_acceso') con una huella del token y se
 * reutiliza mientras el token sea el mismo: basta UNA consulta por inicio de
 * sesion. Otro login en el mismo navegador cambia el token y se vuelve a preguntar.
 *
 * Si la consulta falla (caida, timeout, ruta aun no desplegada en el SSO) NO se
 * bloquea ni se guarda nada: el backend corta igual, y una consulta que no
 * llego no debe dejar fuera a un usuario principal.
 */
class AccesoNotify
{
    public const MOTIVO_SUBUSUARIO = 'SUBUSUARIO_NO_SOPORTADO';

    private const SESION = 'notify_acceso';

    public function __construct(private ExternalApiService $api) {}

    public function handle(Request $request, Closure $next): Response
    {
        $a = $this->acceso();

        if ($a !== null && ($a['acceso'] ?? true) === false
            && ($a['motivo'] ?? null) === self::MOTIVO_SUBUSUARIO) {
            return $this->sinPermiso($request, self::MOTIVO_SUBUSUARIO);
        }

        return $next($request);
    }

    /**
     * @return array|null  la respuesta de `acceso` de ESTE token, o null si no
     *                     se pudo consultar.
     */
    private function acceso(): ?array
    {
        $huella   = hash('sha256', (string) Session::get('access_token'));
        $guardado = Session::get(self::SESION);

        if (is_array($guardado) && ($guardado['huella'] ?? null) === $huella) {
            return $guardado;
        }

        try {
            $r = $this->api->acceso();
        } catch (\Throwable $e) {
            // ConnectionException por timeout o conexion rechazada, entre otras.
            Log::warning('AccesoNotify: fallo la consulta de acceso; se deja pasar', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (!is_array($r) || !array_key_exists('acceso', $r)) {
            return null;
        }

        $r = [
            'acceso' => (bool) $r['acceso'],
            'motivo' => isset($r['motivo']) ? (string) $r['motivo'] : null,
            'huella' => $huella,
        ];
        Session::put(self::SESION, $r);

        return $r;
    }

    /**
     * Una pagina para quien navega, un 403 JSON para el AJAX de las pantallas
     * (un HTML completo en medio de un callback de DataTables no se lee).
     */
    private function sinPermiso(Request $request, string $motivo): Response
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok'      => false,
                'motivo'  => $motivo,
                'message' => 'Notify es solo para el usuario principal de la cuenta. Ingresa con el usuario principal.',
            ], 403);
        }

        return response()->view('acceso.sin-permiso', ['motivo' => $motivo], 403);
    }
}
