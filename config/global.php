<?php

/*
 * Configuracion de negocio de HMNotify.
 *
 * APPS (multi-pais, UNICO .env):
 * Mapping IdAplicacion (core SSO) -> {local, name}.
 * Viene del .env como string JSON. El middleware EnsureAppSelected y
 * el controller AppSelectController lo usan para resolver el nombre
 * de la app cuando getUserApps retorna solo AppCore/AppLocal.
 *
 * Ejemplo del .env:
 * APPS='{"14":{"local":1,"name":"HMMOVIL"},"62":{"local":27,"name":"INKA"}}'
 *
 * Si quieres usarlo como array en PHP:
 *   $apps = config('global.apps');
 *   $nombre = $apps['62']['name'] ?? 'UNKNOWN';
 */

$appsJson = env('APPS', '{}');
$apps     = json_decode($appsJson, true);
if (!is_array($apps)) {
    $apps = [];
}

return [

    'apps' => $apps,

    /*
     * URL publica del webserver de HMNotify. Usado para construir URLs
     * absolutas de imagenes y HTML subidos en el wizard v2.
     */
    'public_url' => rtrim(env('APP_URL', 'https://hmnotify.24hm.net'), '/'),

    /*
     * Ruta relativa (dentro de public/) donde se suben imagenes del wizard.
     */
    'upload_path' => 'assets/upload',

    /*
     * Ruta relativa (dentro de public/) donde se suben HTML del wizard.
     */
    'upload_html_path' => 'assets/upload/HTML',

    /*
     * Tamano maximo de imagen del wizard (en KB).
     */
    'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 2048),

];
