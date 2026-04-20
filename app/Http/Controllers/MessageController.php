<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
     *
     * Pre-carga listGroups y listTipoEntidad (cacheados 30min por UCode/App) para
     * rellenar los select multiples de la pestana "Audiencia" (Nazox multiselect
     * requiere las options iniciales renderizadas desde server).
     */
    public function notificationWizard2()
    {
        $appLocal = Session::get('AppNotify.idLocal');

        $gruposResp = $this->fetchCachedCatalog('Notify/getGrupoSubUsuario', ['app' => $appLocal], 'Groups',   1800);
        $tiposResp  = $this->fetchCachedCatalog('Notify/getTipoEntidad',     [],                  'EntTypes', 1800);

        return view('notificationWizard2', [
            'pageTitle'       => 'Nueva notificacion',
            'listGroups'      => $gruposResp['Groups']   ?? [],
            'listTipoEntidad' => $tiposResp['EntTypes']  ?? [],
        ]);
    }

    /**
     * Llama a un catalogo con cache; si llega vacio, invalida la key y reintenta
     * una vez. Asi un null envenenado (por fallo transitorio) se auto-recupera
     * en la siguiente carga sin necesidad de `php artisan cache:clear`.
     */
    private function fetchCachedCatalog(string $path, array $payload, string $dataKey, int $ttl): array
    {
        $resp = $this->api->cachedPost($path, $payload, $ttl) ?? [];

        if (empty($resp[$dataKey])) {
            $cacheKey = 'sso_api:'.strtoupper((string) Session::get('Pais', 'EC')).':post:'.md5($path.serialize($payload));
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            $resp = $this->api->cachedPost($path, $payload, $ttl) ?? [];
        }

        return $resp;
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

        // Cache 120s por app - evita pegar HMSrvAuth en cada apertura del wizard.
        $response = $this->api->cachedPost('Notify/getDispoByApp', [
            'app' => $appLocal,
        ], 120);

        return response()->json($response ?? ['Error' => true, 'Mensaje' => 'No se pudo obtener dispositivos.']);
    }

    /**
     * Lista de dispositivos con filtros avanzados (tipo entidad, grupo, etc).
     */
    public function getDispositivosAlt(Request $request): JsonResponse
    {
        $appLocal = Session::get('AppNotify.idLocal');
        $template = strtoupper((string) $request->input('template', 'NORMAL'));

        // TTL corto: los dispositivos cambian poco minuto a minuto; 120s es un balance.
        $ttl = 120;

        // Cacheamos el resultado YA MAPEADO (formato DataTable): evita re-procesar
        // 35k filas en cada peticion. Key por app + payload exacto del request.
        $payloadKey = [
            'template'       => $template,
            'numList'        => (string) $request->input('numList', ''),
            'chasisList'     => (string) $request->input('chasisList', ''),
            'motorList'      => (string) $request->input('motorList', ''),
            'idTipoEnt'      => is_array($request->input('idTipoEnt'))  ? implode(',', $request->input('idTipoEnt'))  : (string) $request->input('idTipoEnt'),
            'idSubGrupo'     => is_array($request->input('idSubGrupo')) ? implode(',', $request->input('idSubGrupo')) : (string) $request->input('idSubGrupo'),
            'filtroTipoUser' => (int) $request->input('filtroTipoUser', 3),
            'plataforma'     => (string) $request->input('plat', $request->input('plataforma', '')),
        ];
        $mappedKey = 'hmnotify:dispo_mapped:'.$appLocal.':'.md5(serialize($payloadKey));

        $mapped = Cache::remember($mappedKey, $ttl, function () use ($request, $template, $appLocal, $ttl) {
            if ($template === 'TEMPLATE') {
                if ($request->filled('numList')) {
                    $response = $this->api->cachedPost('Notify/getDispoByNumero', [
                        'numList'      => $request->input('numList'),
                        'idAplicacion' => (string) $appLocal,
                    ], $ttl);
                } else {
                    $response = $this->api->cachedPost('Notify/getDispoByMotorChasis', [
                        'chasisList'   => $request->input('chasisList', ''),
                        'motorList'    => $request->input('motorList', ''),
                        'idAplicacion' => (string) $appLocal,
                    ], $ttl);
                }
            } else {
                $idTipoEnt  = $request->input('idTipoEnt');
                $idSubGrupo = $request->input('idSubGrupo');
                $plat       = $request->input('plat', $request->input('plataforma'));

                $response = $this->api->cachedPost('Notify/getDispoUserByApp', [
                    'app'            => $appLocal,
                    'idTipoEnt'      => is_array($idTipoEnt)  ? implode(',', $idTipoEnt)  : $idTipoEnt,
                    'idSubGrupo'     => is_array($idSubGrupo) ? implode(',', $idSubGrupo) : $idSubGrupo,
                    'filtroTipoUser' => (int) $request->input('filtroTipoUser', 3),
                    'plataforma'     => ($plat === 'TODOS' ? null : $plat),
                ], $ttl);
            }

            return $this->mapDispositivosToDatatable($response, $appLocal);
        });

        return response()->json($mapped);
    }

    /**
     * Convierte la respuesta cruda de HMSrvAuth (clave "Devices" o similar) al
     * contrato que espera el DataTable del wizard (clave "data" con columnas
     * Check/App/DId/Name/NameExt/NGroup/Min/Plat/MDisp/Tipo).
     */
    private function mapDispositivosToDatatable(?array $response, $appLocal): array
    {
        $devices = $response['Devices']
            ?? $response['devices']
            ?? $response['data']
            ?? [];

        $out = ['data' => []];
        foreach ($devices as $i => $item) {
            $name  = $item['User']     ?? $item['Name']  ?? '--';
            $isSub = !empty($item['USubCode']) || !empty($item['IdSubUser']) || ($item['Tipo'] ?? null) === 'SUBUSUARIO';

            $out['data'][] = [
                'Check'   => '<div class="form-check"><input type="checkbox" class="form-check-input" id="ordercheck'.$i.'"></div>',
                'App'     => $appLocal,
                'DId'     => $item['Vid']       ?? $item['DId']    ?? '--',
                'Name'    => $name,
                'NameExt' => [$name, $isSub ? 'SUBUSUARIO' : 'USUARIO'],
                'NGroup'  => $item['GroupName'] ?? $item['NGroup'] ?? '--',
                'Min'     => $item['Min']       ?? '--',
                'Plat'    => $item['Platform']  ?? $item['Plat']   ?? '--',
                'MDisp'   => $item['DevModel']  ?? $item['MDisp']  ?? '--',
                'Tipo'    => $item['EntName']   ?? $item['Tipo']   ?? '--',
            ];
        }
        return $out;
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
     * Envio principal del wizard v2.
     *
     * Acepta los nombres de campo legacy (customFileNoti, htmlNoti,
     * buttons[principal|secundario], programation_h, schedule_*_h,
     * titleCampaing_h, custom_rule_json_h, dispositivos-input). La estructura
     * del payload que se manda a HMSrvAuth (Notify/saveWizard) se mantiene
     * canonica (campaignName, program, scheduleDate, dailyTime, etc.).
     *
     * Bug corregido: legacy tenia case 2 duplicado (HTML + Vencimiento). Solo
     * queda HTML.
     */
    public function postDispositivosSendNew2(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipoNoti'          => 'required|integer|in:0,1,2,4',
            'titleNoti'         => 'nullable|string|max:200',
            'subTitleNoti'      => 'nullable|string|max:500',
            'customFileNoti'    => 'nullable|image|max:'.config('global.upload_max_kb', 2048),
            'htmlNoti'          => 'nullable|string',
            'tipoMode'          => 'nullable|string|max:10',
            'colorTitleNoti'    => 'nullable|string|max:20',
            'colorSubTitleNoti' => 'nullable|string|max:20',
            'colorFondoNoti'    => 'nullable|string|max:20',
            'buttons'           => 'nullable|array',
            'buttons.principal'  => 'nullable|array',
            'buttons.secundario' => 'nullable|array',

            // Hiddens del step Programacion
            'programation_h'        => 'nullable|integer|in:0,1,2,3',
            'schedule_date_h'       => 'nullable|date_format:Y-m-d',
            'schedule_time_h'       => 'nullable|date_format:H:i',
            'schedule_daily_time_h' => 'nullable|date_format:H:i',
            'schedule_daily_start_h'=> 'nullable|date_format:Y-m-d',
            'schedule_daily_end_h'  => 'nullable|date_format:Y-m-d',
            'custom_rule_json_h'    => 'nullable|string',
            'titleCampaing_h'       => 'nullable|string|max:200',
            'subTitleCampaing_h'    => 'nullable|string|max:500',

            // Dispositivos seleccionados en la datatable (JSON string)
            'dispositivos-input'    => 'required|string',
        ]);

        $tipoNoti = (int) $validated['tipoNoti'];
        $title    = $validated['titleNoti']    ?? '';
        $subtitle = $validated['subTitleNoti'] ?? '';

        // -------- Upload imagen (customFileNoti) --------
        $imagenUrl = null;
        $publicUrl = config('global.public_url');

        if ($request->hasFile('customFileNoti')) {
            $file     = $request->file('customFileNoti');
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path(config('global.upload_path')), $filename);
            $imagenUrl = $publicUrl.'/'.config('global.upload_path').'/'.$filename;
        }

        // -------- HTML inline (legacy: guardaba el textarea a /assets/upload/HTML/html_{ts}.html) --------
        $htmlUrl = null;
        if (!empty($validated['htmlNoti'])) {
            $htmlDir  = public_path(config('global.upload_html_path'));
            if (!is_dir($htmlDir)) {
                @mkdir($htmlDir, 0775, true);
            }
            $htmlFile = 'html_'.time().'.html';
            if (@file_put_contents($htmlDir.DIRECTORY_SEPARATOR.$htmlFile, $validated['htmlNoti']) !== false) {
                $htmlUrl = $publicUrl.'/'.config('global.upload_html_path').'/'.$htmlFile;
            }
        }

        // -------- Botones (principal / secundario) --------
        [$btns, $urlLegacy, $wsLegacy, $contactoLegacy] = $this->normalizeButtons(
            $request->input('buttons', []),
            $tipoNoti
        );

        // -------- Resolver (Tipo, Subtipo, TipoAlertaName, MensajeXMPP) --------
        [$tipo, $subtipo, $tipoAlertaName, $mensajeXMPP] = $this->resolveTipoContent(
            $tipoNoti,
            $title,
            $subtitle,
            $imagenUrl,
            $htmlUrl,
            [
                'tipoMode'          => $request->input('tipoMode', '1'),
                'colorTitleNoti'    => $request->input('colorTitleNoti',    '#000000'),
                'colorSubTitleNoti' => $request->input('colorSubTitleNoti', '#000000'),
                'colorFondoNoti'    => $request->input('colorFondoNoti',    '#ffffff'),
                'btns'              => $btns,
            ]
        );

        $program = (int) ($validated['programation_h'] ?? 0);
        $status  = $program === 0 ? 'sent' : 'scheduled';

        $payload = [
            'campaignName'        => $validated['titleCampaing_h']    ?? null,
            'campaignDescription' => $validated['subTitleCampaing_h'] ?? null,
            'app'                 => Session::get('AppNotify.idLocal'),
            'tipoNoti'            => $tipoNoti,
            'tipoAlertaName'      => $tipoAlertaName,
            'tipo'                => $tipo,
            'subtipo'             => $subtipo,
            'titleNoti'           => $title,
            'subTitleNoti'        => $subtitle,
            'imagenUrl'           => $imagenUrl,
            'htmlUrl'             => $htmlUrl,
            'mensajeXMPP'         => $mensajeXMPP,
            'urlLegacy'           => $urlLegacy,
            'whatsappLegacy'      => $wsLegacy,
            'contactoLegacy'      => $contactoLegacy,
            'status'              => $status,
            'program'             => $program,
            'scheduleDate'        => $program === 1 ? ($validated['schedule_date_h']       ?? null) : null,
            'scheduleTime'        => $program === 1 ? ($validated['schedule_time_h']       ?? null) : null,
            'dailyTime'           => $program === 2 ? ($validated['schedule_daily_time_h'] ?? null) : null,
            'dailyStartDate'      => $program === 2 ? ($validated['schedule_daily_start_h']?? null) : null,
            'dailyEndDate'        => $program === 2 ? ($validated['schedule_daily_end_h']  ?? null) : null,
            'customRuleJson'      => $validated['custom_rule_json_h'] ?? null,
            'jsonDetalle'         => $validated['dispositivos-input'],
        ];

        $response = $this->api->post('Notify/saveWizard', $payload);

        if (!is_array($response) || ($response['error'] ?? $response['Error'] ?? true)) {
            Log::warning('saveWizard respondio con error', [
                'response' => $response,
                'payload'  => array_merge($payload, ['jsonDetalle' => '[truncated]']),
            ]);
        }

        // Contrato con el JS (legacy): {error: bool, message: string}
        return response()->json($response ?? ['error' => true, 'message' => 'No se pudo guardar la campana.']);
    }

    /**
     * Normaliza botones del wizard v2.
     *
     * Input crudo:
     *   buttons[principal|secundario] = [text, type, url, phone, bg, text_color]
     *
     * Output:
     *   - $btns: array de botones normalizados (Role PRI|SEC, Type, Text, Url|Phone, BgColor, TextColor)
     *   - $urlLegacy, $wsLegacy, $contactoLegacy: primer valor de cada tipo (para NotifyLog legacy).
     *
     * Solo se incluyen botones si tipoNoti === 1 (Multimedia).
     */
    private function normalizeButtons(array $raw, int $tipoNoti): array
    {
        $normalize = function (?array $btn, string $role): ?array {
            if (empty($btn)) return null;
            $text = trim((string) ($btn['text'] ?? ''));
            $type = (string) ($btn['type'] ?? '');
            if ($text === '' || $type === '') return null;

            $common = [
                'Role'      => $role,
                'Type'      => $type,
                'Text'      => $text,
                'BgColor'   => (string) ($btn['bg']         ?? '#eeeeee'),
                'TextColor' => (string) ($btn['text_color'] ?? '#000000'),
            ];

            if ($type === 'url') {
                $url = trim((string) ($btn['url'] ?? ''));
                return $url === '' ? null : array_merge($common, ['Url' => $url]);
            }

            if ($type === 'call' || $type === 'whatsapp') {
                $phone = trim((string) ($btn['phone'] ?? ''));
                return $phone === '' ? null : array_merge($common, ['Phone' => $phone]);
            }

            return null;
        };

        $btns = $tipoNoti === 1
            ? array_values(array_filter([
                $normalize($raw['principal']  ?? null, 'PRI'),
                $normalize($raw['secundario'] ?? null, 'SEC'),
              ]))
            : [];

        $urlLegacy = $wsLegacy = $contactoLegacy = null;
        foreach ($btns as $b) {
            if ($b['Type'] === 'url'      && $urlLegacy      === null) $urlLegacy      = $b['Url']   ?? null;
            if ($b['Type'] === 'whatsapp' && $wsLegacy       === null) $wsLegacy       = $b['Phone'] ?? null;
            if ($b['Type'] === 'call'     && $contactoLegacy === null) $contactoLegacy = $b['Phone'] ?? null;
        }

        return [$btns, $urlLegacy, $wsLegacy, $contactoLegacy];
    }

    /**
     * Resuelve (Tipo, Subtipo, TipoAlertaName, MensajeXMPP) segun el tipoNoti.
     *   0 Informativa: Tipo=7, Subtipo=2
     *   1 Multimedia:  Tipo=2, Subtipo=6
     *   2 HTML:        Tipo=7, Subtipo=2
     *   4 Texto:       Tipo=1, Subtipo=6
     */
    private function resolveTipoContent(
        int $tipoNoti,
        string $title,
        string $subtitle,
        ?string $imagenUrl,
        ?string $htmlUrl,
        array $extra
    ): array {
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
                $tipoMode   = $extra['tipoMode']          ?? '1';
                $colorTitle = $extra['colorTitleNoti']    ?? '#000000';
                $colorSub   = $extra['colorSubTitleNoti'] ?? '#000000';
                $colorFondo = $extra['colorFondoNoti']    ?? '#ffffff';
                $isDefault  = $colorTitle === '#000000' && $colorSub === '#000000' && $colorFondo === '#ffffff';
                $mdl        = $isDefault
                    ? [$tipoMode]
                    : [$tipoMode, $colorTitle, $colorSub, $colorFondo];

                $payload = [
                    'Multimedia' => [
                        'Titulo'    => $title,
                        'Subtitulo' => $subtitle,
                        'Image'     => $imagenUrl ?? '',
                        'Mdl'       => $mdl,
                    ],
                ];
                if (!empty($extra['btns'])) {
                    $payload['Multimedia']['Btns'] = $extra['btns'];
                }
                return [2, 6, 'Multimedia', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];

            case 2: // HTML
                return [7, 2, 'HTML', json_encode([
                    'Html' => [
                        'Titulo'    => $title,
                        'Subtitulo' => $subtitle,
                        'Image'     => $imagenUrl ?? '',
                        'Url'       => $htmlUrl ?? '',
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];

            case 4: // Texto
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
