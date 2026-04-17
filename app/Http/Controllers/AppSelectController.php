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
        $response = $this->api->post('Notify/getUserApps');

        if (!is_array($response) || ($response['Error'] ?? true)) {
            return view('select-app', [
                'apps'  => [],
                'error' => $response['Mensaje'] ?? 'No se pudo consultar las apps autorizadas.',
            ]);
        }

        $rawApps = $response['Apps'] ?? [];
        $apps    = $this->enrichApps($rawApps);

        // 0 apps: el usuario no tiene permiso en UsuarioAplicacionNotify
        if (empty($apps)) {
            return view('select-app', [
                'apps'  => [],
                'error' => 'Tu usuario no tiene apps autorizadas para HMNotify. Contacta al administrador.',
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
        ]);
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
                'name'    => $meta['name']  ?? 'APP_'.$idCore,
                'color'   => $meta['color'] ?? '#556ee6',
            ];
        }

        return $result;
    }

    private function setSessionApp(array $app): void
    {
        // Branding canonico vive en HMSrvAuth/config/apps.php (logo CDN, color,
        // titulo, favicon, bg). Se consulta una vez en la seleccion y se cachea
        // en sesion para que layout/wizard/preview no vuelvan a pegar al API.
        $branding = $this->api->cachedPost('Notify/getAppBranding', [
            'app'     => $app['idCore'],
            'idLocal' => $app['idLocal'],
        ], 1800);

        $isValid = is_array($branding) && empty($branding['Error']);

        Session::put('AppNotify', [
            'idCore'   => $app['idCore'],
            'idLocal'  => $app['idLocal'],
            'idName'   => $isValid ? ($branding['id_name'] ?? null) : null,
            'name'     => $isValid ? ($branding['name']     ?? $app['name']) : $app['name'],
            'title'    => $isValid ? ($branding['title']    ?? null) : null,
            'color'    => $isValid ? ($branding['color']    ?? $app['color'] ?? '#556ee6') : ($app['color'] ?? '#556ee6'),
            'logo'     => $isValid ? ($branding['logo']     ?? null) : null,
            'favicon'  => $isValid ? ($branding['favicon']  ?? null) : null,
            'bg'       => $isValid ? ($branding['bg']       ?? null) : null,
            'img_pri'  => $isValid ? ($branding['img_pri']  ?? null) : null,
        ]);
    }
}
