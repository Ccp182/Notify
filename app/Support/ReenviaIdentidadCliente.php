<?php

namespace App\Support;

/**
 * ReenviaIdentidadCliente
 * -----------------------------------------------------------------------------
 * Reenvía la IP y el UserAgent del USUARIO FINAL al SSO (HMSrvAuth) para que su
 * bitácora de actividad pueda identificar quién hizo cada llamada.
 *
 * El problema que resuelve: esta app llama al SSO DESDE EL SERVIDOR
 * (Laravel -> Guzzle), así que sin estas cabeceras la bitácora registra la IP de
 * Osiris (10.100.97.138) y el UserAgent "GuzzleHttp/7". Eso no identifica a
 * nadie: si mañana hay que responder "¿quién hizo esto?", la IP no aporta nada.
 * Con las cabeceras, la columna Ip de PX_LOG_EC.dbo.SSO_LogActividad trae la IP
 * pública real del usuario.
 *
 * Seguridad: HMSrvAuth SOLO acepta estas cabeceras si la conexión viene de la red
 * interna. Si confiara siempre en ellas, cualquiera desde internet podría
 * falsear su IP en la bitácora con solo enviarlas.
 *
 * Va en un trait (y no como método privado de una sola clase) porque las
 * llamadas al SSO salen de VARIOS puntos: el ExternalApiService, el middleware
 * que revalida el token contra /me y /apps, y el SSOController del callback de
 * login. Si el método viviera solo en el servicio, esas otras llamadas seguirían
 * registrando la IP de Guzzle.
 *
 * Uso:
 *   use App\Support\ReenviaIdentidadCliente;
 *   Http::withToken($token)->withHeaders($this->cabecerasCliente())->get(...);
 */
trait ReenviaIdentidadCliente
{
    protected function cabecerasCliente(): array
    {
        $peticion = request();
        if ($peticion === null) {
            return [];   // contexto CLI / cola: no hay usuario final
        }

        $cabeceras = [];

        $ip = $peticion->ip();
        if ($ip !== null && $ip !== '') {
            $cabeceras['X-HM-Client-Ip'] = $ip;
        }

        $agente = (string) $peticion->userAgent();
        if ($agente !== '') {
            $cabeceras['X-HM-Client-Agent'] = substr($agente, 0, 300);
        }

        return $cabeceras;
    }
}
