<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * AppSelectController
 * -----------------------------------------------------------------------------
 * Pantalla post-login de seleccion de app (Opcion B).
 *
 * Flujo:
 *   GET  /select-app
 *     - Llama /api/{country}/Notify/getUserApps
 *     - Si 0 apps -> vista de error (sin permiso).
 *     - Si 1 app  -> auto-select + redirect /dashboard.
 *     - Si >1     -> vista con combo.
 *   POST /select-app
 *     - Valida que el appCore enviado este entre las apps autorizadas.
 *     - Arma Session::AppNotify con {idCore, idLocal, name, color}.
 *     - Redirige a /dashboard.
 */
class AppSelectController extends Controller
{
    public function __construct(protected ExternalApiService $api)
    {
    }

    public function show()
    {
        $hmsso    = $this->fetchHmssoBranding();
        $response = $this->api->post('Notify/getUserApps');

        if (!is_array($response) || ($response['Error'] ?? true)) {
            return view('select-app', [
                'apps'  => [],
                'error' => $response['Mensaje'] ?? 'No se pudo consultar las apps autorizadas.',
                'hmsso' => $hmsso,
            ]);
        }

        $rawApps = $response['Apps'] ?? [];
        $apps    = $this->enrichApps($rawApps);
        $apps = array_values(array_filter(
            array_map(fn($a) => $this->withBranding($a), $apps),
            fn($a) => $a['_branding_ok'] ?? false
        ));

        // 0 apps: el usuario no tiene permiso en UsuarioAplicacionNotify
        if (empty($apps)) {
            return view('select-app', [
                'apps'  => [],
                'error' => 'Tu usuario no tiene apps autorizadas para HMNotify. Contacta al administrador.',
                'hmsso' => $hmsso,
            ]);
        }

        // 1 app: auto-seleccionar y saltar al dashboard
        if (count($apps) === 1) {
            $this->setSessionApp($apps[0]);
            return redirect()->route('dashboard');
        }

        // >1 apps: mostrar combo
        return view('select-app', [
            'apps'  => $apps,
            'error' => null,
            'hmsso' => $hmsso,
        ]);
    }

    /**
     * Branding de HMSSO para el encabezado del selector de apps (logo + color primario).
     */
    private function fetchHmssoBranding(): array
    {
        try {
            $b = $this->api->cachedPost('Notify/getAppBranding', [
                'idName' => 'HMSSO',
            ], 3600);
        } catch (\Throwable $e) {
            $b = null;
        }

        $ok = is_array($b) && empty($b['Error']);
        return [
            'logo'  => $ok ? ($b['logo']  ?? null) : null,
            'color' => $ok ? ($b['color'] ?? '#556ee6') : '#556ee6',
            'name'  => $ok ? ($b['name']  ?? 'HMSSO') : 'HMSSO',
        ];
    }

    public function select(Request $request)
    {
        $request->validate([
            'appCore' => ['required', 'integer'],
        ]);

        $response = $this->api->post('Notify/getUserApps');
        $rawApps  = $response['Apps'] ?? [];
        $apps     = $this->enrichApps($rawApps);

        $selectedAppCore = (int) $request->input('appCore');
        $selected = collect($apps)->firstWhere('idCore', $selectedAppCore);
        if ($selected) $selected = $this->withBranding($selected);

        if (!$selected) {
            return back()->withErrors([
                'appCore' => 'La app seleccionada no esta autorizada para tu usuario.',
            ]);
        }

        $this->setSessionApp($selected);
        return redirect()->route('dashboard');
    }

    /**
     * Enriquece el array de apps del endpoint (AppCore + AppLocal) con
     * el nombre y color resueltos desde .env APPS.
     */
    private function enrichApps(array $rawApps): array
    {
        $appsMap = config('global.apps', []);
        $result  = [];

        foreach ($rawApps as $row) {
            $idCore  = (int) ($row['AppCore']  ?? 0);
            $idLocal = (int) ($row['AppLocal'] ?? 0);

            if ($idCore === 0) continue;

            $meta = $appsMap[(string) $idCore] ?? null;
            if (!$meta) {
                // App desconocida en el mapping local: la incluimos con nombre por default
                $meta = [
                    'local' => $idLocal,
                    'name'  => 'APP_'.$idCore,
                    'color' => '#556ee6',
                ];
            }

            $result[] = [
                'idCore'  => $idCore,
                'idLocal' => $idLocal ?: (int) ($meta['local'] ?? 0),
                'name'    => $meta['name']   ?? 'APP_'.$idCore,
                'idName'  => $meta['idName'] ?? $meta['name'] ?? null,
                'color'   => $meta['color']  ?? '#556ee6',
            ];
        }

        return $result;
    }

    /**
     * Enriquece un app con los datos de branding del HMSrvAuth (cacheado).
     * Fuente de verdad: HMSrvAuth/config/apps.php via Notify/getAppBranding.
     */
    private function withBranding(array $app): array
    {
        try {
            $branding = $this->api->cachedPost('Notify/getAppBranding', [
                'idName'  => $app['idName']  ?? null,
                'name'    => $app['name']    ?? null,
                'app'     => $app['idCore']  ?? null,
                'idLocal' => $app['idLocal'] ?? null,
            ], 1800);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getAppBranding failed', [
                'app' => $app, 'msg' => $e->getMessage(),
            ]);
            $branding = null;
        }

        $ok = is_array($branding) && empty($branding['Error']);

        return array_merge($app, [
            '_branding_ok' => $ok,
            'idName'     => $ok ? ($branding['id_name']    ?? $app['idName'] ?? null) : ($app['idName'] ?? null),
            'name'       => $ok ? ($branding['name']       ?? $app['name'])  : $app['name'],
            'title'      => $ok ? ($branding['title']      ?? $app['name'])  : $app['name'],
            'color'      => $ok ? ($branding['color']      ?? $app['color'] ?? '#556ee6') : ($app['color'] ?? '#556ee6'),
            'logo'          => $ok ? ($branding['logo']          ?? null) : null,
            'logo_white'    => $ok ? ($branding['logo_white']    ?? null) : null,
            'isotype'       => $ok ? ($branding['isotype']       ?? null) : null,
            'isotype_white' => $ok ? ($branding['isotype_white'] ?? null) : null,
            'favicon'       => $ok ? ($branding['favicon']       ?? null) : null,
            'bg'            => $ok ? ($branding['bg']            ?? null) : null,
            'img_pri'       => $ok ? ($branding['img_pri']       ?? null) : null,
        ]);
    }

    private function setSessionApp(array $app): void
    {
        $app = isset($app['logo']) ? $app : $this->withBranding($app);

        Session::put('AppNotify', [
            'idCore'     => $app['idCore'],
            'idLocal'    => $app['idLocal'],
            'idName'     => $app['idName']     ?? null,
            'name'       => $app['name']       ?? null,
            'title'      => $app['title']      ?? $app['name'] ?? null,
            'color'      => $app['color']      ?? '#556ee6',
            'logo'          => $app['logo']          ?? null,
            'logo_white'    => $app['logo_white']    ?? null,
            'isotype'       => $app['isotype']       ?? null,
            'isotype_white' => $app['isotype_white'] ?? null,
            'favicon'       => $app['favicon']       ?? null,
            'bg'            => $app['bg']            ?? null,
            'img_pri'       => $app['img_pri']       ?? null,
        ]);
    }
}
