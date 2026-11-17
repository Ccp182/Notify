<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * IP real del usuario para reenviarla al SSO en X-HM-Client-Ip (2026-11-16, ver SSO/ip-cliente-imperva.html).
 *
 * La decide QUIEN CONECTA: si es un nodo de Imperva, la IP viene en Incap-Client-IP (Imperva la reescribe);
 * si no, es la de la conexion y no se lee ninguna cabecera (por fw26 o fw28 se pueden inventar).
 * El SSO aplica la misma regla (HMSrvAuth/app/Support/IpCliente.php). Copia identica en cada app cliente.
 */
final class IpCliente
{
    /** Rangos oficiales de Imperva (https://my.imperva.com/api/integration/v1/ips, 16/11/2026). */
    public const IMPERVA = [
        '199.83.128.0/21', '198.143.32.0/19', '149.126.72.0/21', '103.28.248.0/22', '185.11.124.0/22',
        '192.230.64.0/18', '45.64.64.0/22', '107.154.0.0/16', '45.60.0.0/16', '45.223.0.0/16',
        '131.125.128.0/17', '2a02:e980::/29',
    ];

    public static function de(?Request $peticion): ?string
    {
        if ($peticion === null) {
            return null;
        }
        $remota = (string) $peticion->server->get('REMOTE_ADDR', '');
        if (self::enRangos($remota, self::IMPERVA)) {
            $incap = trim((string) $peticion->headers->get('Incap-Client-IP', ''));
            if ($incap !== '' && filter_var($incap, FILTER_VALIDATE_IP)) {
                return $incap;
            }
        }

        return $remota !== '' ? $remota : null;
    }

    /** Cabeceras de identidad para las llamadas al SSO (IP y navegador del usuario). */
    public static function cabeceras(?Request $peticion): array
    {
        if ($peticion === null) {
            return [];
        }

        return array_filter([
            'X-HM-Client-Ip'    => (string) self::de($peticion),
            'X-HM-Client-Agent' => substr((string) $peticion->userAgent(), 0, 300),
        ], fn ($v) => $v !== '');
    }

    public static function enRangos(string $ip, array $rangos): bool
    {
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        foreach ($rangos as $r) {
            [$red, $bits] = array_pad(explode('/', (string) $r, 2), 2, null);
            $redBin = @inet_pton(trim($red));
            if ($redBin === false || strlen($redBin) !== strlen($bin)) {
                continue;
            }
            $bits = $bits === null ? strlen($bin) * 8 : max(0, min((int) $bits, strlen($bin) * 8));
            $bytes = intdiv($bits, 8);
            $resto = $bits % 8;
            if (substr($bin, 0, $bytes) !== substr($redBin, 0, $bytes)) {
                continue;
            }
            if ($resto === 0) {
                return true;
            }
            $mascara = (0xFF << (8 - $resto)) & 0xFF;
            if ((ord($bin[$bytes]) & $mascara) === (ord($redBin[$bytes]) & $mascara)) {
                return true;
            }
        }

        return false;
    }
}
