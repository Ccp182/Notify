<?php
// app/Support/Tenant.php
namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Tenant
{
    public static function setCountryConnection(string $country): void
    {
        $prev = config('database.default');
        $new  = $country === 'EC' ? 'sqlsrv' : 'sqlsrv'.strtolower($country);

        // Variables de entorno por país (si las usas)
        Config::set('Pais', $country);
        Config::set('PaisSuf', $country === 'EC' ? '' : '_'.$country);

        // Cambiar conexión por defecto
        Config::set('database.default', $new);

        // Limpiar conexiones para evitar pool colgado en la anterior
        if ($prev && $prev !== $new) {
            DB::purge($prev);
        }
        DB::purge($new);

        // Reabrir con la nueva
        DB::reconnect($new);
    }
}
