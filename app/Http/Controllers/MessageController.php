<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * MessageController
 * -----------------------------------------------------------------------------
 * Logica del negocio de HMNotify:
 *   - dashboard: listado de dispositivos de la app seleccionada.
 *   - wizard v2: formulario multi-step para crear/programar notificacion.
 *   - endpoints proxy para el front: getDispositivos, getDispositivosAlt,
 *     envio, template csv.
 *
 * Solo se migra el flujo v2 del legacy (postDispositivosSendNew2).
 * Bug corregido: el case 2 duplicado (Vencimiento) se elimina, solo queda HTML.
 */
class MessageController extends Controller
{
    public function __construct(protected ExternalApiService $api)
    {
    }

    /**
     * Dashboard de dispositivos de la app seleccionada.
     */
    public function dashboard()
    {
        return view('message', [
            'pageTitle' => 'Dashboard',
        ]);
    }

    /**
     * Vista del wizard v2.
     */
    public function notificationWizard2()
    {
        return view('notificationWizard2', [
            'pageTitle' => 'Nueva notificacion',
        ]);
    }

    /* =========================================================================
     * Endpoints AJAX (consumidos por el front del wizard/dashboard)
     * =====================================================================*/

    /**
     * Lista de dispositivos por app (sin filtros).
     */
    public function getDispositivos(Request $request): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $response = $this->api->post('Notify/getDispoByApp', [
            'app' => $appLocal,
        ]);

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo obtener dispositivos.']);
    }

    /**
     * Lista de dispositivos con filtros avanzados (tipo entidad, grupo, etc).
     */
    public function getDispositivosAlt(Request $request): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $response = $this->api->post('Notify/getDispoUserByApp', [
            'app'            => $appLocal,
            'idTipoEnt'      => $request->input('idTipoEnt', '0'),
            'idSubGrupo'     => $request->input('idSubGrupo', '0'),
            'filtroTipoUser' => (int) $request->input('filtroTipoUser', 3),
            'plataforma'     => $request->input('plataforma'),
        ]);

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo filtrar dispositivos.']);
    }

    /**
     * Catalogos combinados para el wizard (tipos entidad, plataformas, grupos).
     */
    public function getCatalogos(): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $tipos      = $this->api->cachedPost('Notify/getTipoEntidad',    [],                      120);
        $plataformas = $this->api->cachedPost('Notify/getPlataformas',    ['app' => $appLocal],    120);
        $grupos     = $this->api->cachedPost('Notify/getGrupoSubUsuario', ['app' => $appLocal],    120);

        return response()->json([
            'Error'      => false,
            'EntTypes'   => $tipos['EntTypes']   ?? [],
            'Platforms'  => $plataformas['Platforms'] ?? [],
            'Groups'     => $grupos['Groups']     ?? [],
        ]);
    }

    /**
     * Envio principal del wizard v2 (case corregido: HTML sin Vencimiento).
     */
    public function postDispositivosSendNew2(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipoNoti'        => 'required|integer|in:0,1,2,4',
            'titleNoti'       => 'nullable|string|max:200',
            'subTitleNoti'    => 'nullable|string|max:500',
            'imagen'          => 'nullable|image|max:'.config('global.upload_max_kb', 2048),
            'html'            => 'nullable|file|mimes:html,htm|max:'.config('global.upload_max_kb', 2048),
            'urlNoti'         => 'nullable|url|max:500',
            'whatsapp'        => 'nullable|string|max:20',
            'contacto'        => 'nullable|string|max:20',
            'tipoMode'        => 'nullable|string',
            'colorTitleNoti'  => 'nullable|string|max:20',
            'colorSubTitleNoti'=> 'nullable|string|max:20',
            'colorFondoNoti'  => 'nullable|string|max:20',
            'btns'            => 'nullable|array',
            'program'         => 'required|integer|in:0,1,2,3',
            'scheduleDate'    => 'nullable|date_format:Y-m-d',
            'scheduleTime'    => 'nullable|date_format:H:i',
            'dailyTime'       => 'nullable|date_format:H:i',
            'dailyStartDate'  => 'nullable|date_format:Y-m-d',
            'dailyEndDate'    => 'nullable|date_format:Y-m-d',
            'targets'         => 'required|array|min:1',
            'campaignName'    => 'nullable|string|max:200',
            'campaignDescription' => 'nullable|string|max:500',
        ]);

        // Uploads: imagen y HTML al disco 'uploads' (public/assets/upload).
        $imagenUrl = null;
        $htmlUrl   = null;
        $publicUrl = config('global.public_url');

        if ($request->hasFile('imagen')) {
            $file     = $request->file('imagen');
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path(config('global.upload_path')), $filename);
            $imagenUrl = $publicUrl.'/'.config('global.upload_path').'/'.$filename;
        }

        if ($request->hasFile('html')) {
            $file     = $request->file('html');
            $filename = 'html_'.time().'.html';
            $file->move(public_path(config('global.upload_html_path')), $filename);
            $htmlUrl = $publicUrl.'/'.config('global.upload_html_path').'/'.$filename;
        }

        // Resolver Tipo / Subtipo / TipoAlertaName / MensajeXMPP segun tipoNoti
        [$tipo, $subtipo, $tipoAlertaName, $mensajeXMPP] = $this->resolveTipoContent(
            $validated,
            $imagenUrl,
            $htmlUrl
        );

        $status = $validated['program'] == 0 ? 'sent' : 'scheduled';

        $payload = [
            'campaignName'        => $validated['campaignName']        ?? null,
            'campaignDescription' => $validated['campaignDescription'] ?? null,
            'app'                 => Session::get('AppNotify.idLocal'),
            'tipoNoti'            => $validated['tipoNoti'],
            'tipoAlertaName'      => $tipoAlertaName,
            'tipo'                => $tipo,
            'subtipo'             => $subtipo,
            'titleNoti'           => $validated['titleNoti']    ?? null,
            'subTitleNoti'        => $validated['subTitleNoti'] ?? null,
            'imagenUrl'           => $imagenUrl,
            'htmlUrl'             => $htmlUrl,
            'mensajeXMPP'         => $mensajeXMPP,
            'urlLegacy'           => $validated['urlNoti']  ?? null,
            'whatsappLegacy'      => $validated['whatsapp'] ?? null,
            'contactoLegacy'      => $validated['contacto'] ?? null,
            'status'              => $status,
            'program'             => $validated['program'],
            'scheduleDate'        => $validated['scheduleDate']   ?? null,
            'scheduleTime'        => $validated['scheduleTime']   ?? null,
            'dailyTime'           => $validated['dailyTime']      ?? null,
            'dailyStartDate'      => $validated['dailyStartDate'] ?? null,
            'dailyEndDate'        => $validated['dailyEndDate']   ?? null,
            'customRuleJson'      => null,
            'jsonDetalle'         => json_encode($validated['targets']),
        ];

        $response = $this->api->post('Notify/saveWizard', $payload);

        if (!is_array($response) || ($response['Error'] ?? true)) {
            Log::warning('saveWizard respondio con error', [
                'response' => $response,
                'payload'  => array_merge($payload, ['jsonDetalle' => '[truncated]']),
            ]);
        }

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo guardar la campana.']);
    }

    /**
     * Resuelve (Tipo, Subtipo, TipoAlertaName, MensajeXMPP) segun el tipoNoti.
     * Segun las reglas confirmadas con el legacy:
     *   0 Informativa: Tipo=7, Subtipo=2
     *   1 Multimedia:  Tipo=2, Subtipo=6
     *   2 HTML:        Tipo=7, Subtipo=2
     *   4 Texto:       Tipo=1, Subtipo=6
     */
    private function resolveTipoContent(array $v, ?string $imagenUrl, ?string $htmlUrl): array
    {
        $tipoNoti      = (int) $v['tipoNoti'];
        $title         = $v['titleNoti']    ?? '';
        $subtitle      = $v['subTitleNoti'] ?? '';
        $url           = $v['urlNoti']      ?? '';

        switch ($tipoNoti) {
            case 0: // Informativa
                return [7, 2, 'Informativa', json_encode([
                    'Informativa' => [
                        'Titulo'    => $title,
                        'Subtitulo' => $subtitle,
                        'Image'     => $imagenUrl ?? '',
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];

            case 1: // Multimedia
                $tipoMode      = $v['tipoMode']         ?? 'light';
                $colorTitle    = $v['colorTitleNoti']   ?? '#000000';
                $colorSub      = $v['colorSubTitleNoti']?? '#000000';
                $colorFondo    = $v['colorFondoNoti']   ?? '#ffffff';
                $isDefault     = $colorTitle === '#000000'
                              && $colorSub  === '#000000'
                              && $colorFondo === '#ffffff';
                $mdl           = $isDefault ? [$tipoMode] : [$tipoMode, $colorTitle, $colorSub, $colorFondo];

                $payload = [
                    'Multimedia' => [
                        'Titulo'    => $title,
                        'Subtitulo' => $subtitle,
                        'Image'     => $imagenUrl ?? '',
                        'Mdl'       => $mdl,
                    ],
                ];
                if (!empty($v['btns'])) {
                    $payload['Multimedia']['Btns'] = $v['btns'];
                }
                return [2, 6, 'Multimedia', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];

            case 2: // HTML (BUG CORREGIDO: el legacy tenia case 2 duplicado con 'Vencimiento', se elimina)
                return [7, 2, 'HTML', json_encode([
                    'Html' => [
                        'Titulo'    => $title,
                        'Subtitulo' => $subtitle,
                        'Image'     => $imagenUrl ?? '',
                        'Url'       => $htmlUrl ?? '',
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];

            case 4: // Texto
                return [1, 6, 'Texto', $subtitle];

            default:
                return [1, 6, 'Texto', $subtitle];
        }
    }

    /**
     * Envio masivo por CSV de chasis/motor.
     */
    public function postDispoTemplateSendNew(Request $request): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $validated = $request->validate([
            'chasisList' => 'nullable|string',
            'motorList'  => 'nullable|string',
        ]);

        $response = $this->api->post('Notify/getDispoByMotorChasis', [
            'chasisList'   => $validated['chasisList'] ?? '',
            'motorList'    => $validated['motorList']  ?? '',
            'idAplicacion' => (string) $appLocal,
        ]);

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo consultar dispositivos CSV.']);
    }

    /**
     * Envio masivo por CSV de numeros (Min).
     */
    public function postDispoTemplateNumSendNew(Request $request): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $validated = $request->validate([
            'numList' => 'required|string',
        ]);

        $response = $this->api->post('Notify/getDispoByNumero', [
            'numList'      => $validated['numList'],
            'idAplicacion' => (string) $appLocal,
        ]);

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo consultar dispositivos por numero.']);
    }
}
