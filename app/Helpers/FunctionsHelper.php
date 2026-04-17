<?php

/*
 * FunctionsHelper
 * -----------------------------------------------------------------------------
 * Helpers globales de HMNotify. Migrado del legacy (Laravel 8) conservando
 * solo las funciones que el flujo SSO + wizard v2 realmente usa.
 *
 * Se carga automaticamente via composer.json -> autoload.files.
 */

if (!function_exists('validVarArray')) {
    /**
     * True si $array[$campo] esta seteado, no es null y tiene longitud > 0.
     * (el legacy excluia strings vacios).
     */
    function validVarArray($array, $campo): bool
    {
        return is_array($array)
            && isset($array[$campo])
            && $array[$campo] !== null
            && strlen((string) $array[$campo]) > 0;
    }
}

if (!function_exists('validVarArrayEmpty')) {
    /**
     * True si $array[$campo] esta seteado y no es null (permite string vacio).
     */
    function validVarArrayEmpty($array, $campo): bool
    {
        return is_array($array)
            && isset($array[$campo])
            && $array[$campo] !== null;
    }
}

if (!function_exists('validVarArrayComplex')) {
    /**
     * True si $array[$campo] esta seteado y no esta vacio (segun empty()).
     */
    function validVarArrayComplex($array, $campo): bool
    {
        return is_array($array)
            && isset($array[$campo])
            && !empty($array[$campo]);
    }
}

if (!function_exists('getVarArray')) {
    /**
     * Retorna $array[$campo] con cast al tipo especificado, o null si no existe.
     */
    function getVarArray($array, $campo, string $tipo = 'string')
    {
        if (!validVarArrayEmpty($array, $campo)) {
            return null;
        }
        $v = $array[$campo];
        return match ($tipo) {
            'int'    => (int) $v,
            'float'  => (float) $v,
            'bool'   => (bool) $v,
            default  => (string) $v,
        };
    }
}

if (!function_exists('setVarArray')) {
    /**
     * Copia $row[$campo] a $target[$campoNew] si $row[$campo] es valido.
     * Retorna $target (inmutable).
     */
    function setVarArray(array $target, array $row, string $campo, string $campoNew): array
    {
        if (validVarArray($row, $campo)) {
            $target[$campoNew] = $row[$campo];
        }
        return $target;
    }
}

if (!function_exists('setVarArrayT')) {
    /**
     * Copia $row[$campo] a $target[$campoNew] con cast opcional.
     * Mismo retorno: $target modificado.
     */
    function setVarArrayT(array $target, array $row, string $campo, string $campoNew, string $tipo = 'string'): array
    {
        if (!validVarArrayEmpty($row, $campo)) {
            return $target;
        }
        $target[$campoNew] = match ($tipo) {
            'int'    => (int) $row[$campo],
            'float'  => (float) $row[$campo],
            'bool'   => (bool) $row[$campo],
            default  => (string) $row[$campo],
        };
        return $target;
    }
}

if (!function_exists('setVarArrayCustom')) {
    /**
     * Asigna un valor custom a $target[$campoNew] si el valor no es null.
     */
    function setVarArrayCustom(array $target, string $campoNew, $value): array
    {
        if ($value !== null) {
            $target[$campoNew] = $value;
        }
        return $target;
    }
}

if (!function_exists('getIPAddress')) {
    /**
     * Retorna la IP real del cliente considerando proxies.
     */
    function getIPAddress(): string
    {
        $candidates = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($candidates as $key) {
            if (!empty($_SERVER[$key] ?? null)) {
                $ip = explode(',', $_SERVER[$key])[0];
                return trim($ip);
            }
        }
        return '0.0.0.0';
    }
}

if (!function_exists('truncate')) {
    /**
     * Trunca $text a $length caracteres y agrega ellipsis.
     */
    function truncate(string $text, int $length = 50, string $ellipsis = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - mb_strlen($ellipsis)).$ellipsis;
    }
}

if (!function_exists('hex2rgbStr')) {
    /**
     * Convierte '#E62932' a '230, 41, 50' (formato que Bootstrap 5 espera
     * para --bs-primary-rgb). Devuelve fallback gris si el hex es invalido.
     */
    function hex2rgbStr(?string $hex): string
    {
        if (!$hex) return '85, 110, 230';
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '85, 110, 230';
        }
        return sprintf(
            '%d, %d, %d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }
}
