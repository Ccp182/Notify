<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * CampainController
 * -----------------------------------------------------------------------------
 * Modulo "Campanas" de HMNotify. Cada campana corresponde 1:1 a un envio
 * (NotifyCampaign + NotifySend + NotifySchedule). El usuario solo ve sus
 * propias campanas: el backend (HMSrvAuth::NotifyController::listarCampanas)
 * fuerza @CreatedBy = username del token, el cliente no puede ver las de
 * otros usuarios.
 *
 * Rutas:
 *   - GET  /campains          (vista: listado con DataTable + filtro de fechas)
 *   - POST /campains/list     (AJAX: trae data para el DataTable)
 */
class CampainController extends Controller
{
    public function __construct(protected ExternalApiService $api)
    {
    }

    /**
     * Renderiza la vista del listado de campanas.
     * La carga de datos es via AJAX (endpoint list()), no server-side.
     */
    public function index()
    {
        return view('campains.index', [
            'pageTitle' => 'Campanas',
        ]);
    }

    /**
     * Endpoint AJAX para el DataTable. Acepta filtros opcionales:
     *   - fechaInicio: YYYY-MM-DD
     *   - fechaFin:    YYYY-MM-DD
     *
     * El scope por usuario (createdBy) y el app se resuelven server-side:
     *   - createdBy: lo fuerza HMSrvAuth desde el token OAuth.
     *   - app:       session('AppNotify.idLocal') de la app seleccionada
     *                (consistente con MessageController@send que guarda
     *                NotifyCampaign.App = idLocal al crear la campana).
     */
    public function list(Request $request): JsonResponse
    {
        $appCore = Session::get('AppNotify.idLocal');

        $payload = [
            'app'         => $appCore,
            'fechaInicio' => $request->input('fechaInicio') ?: null,
            'fechaFin'    => $request->input('fechaFin')    ?: null,
        ];

        // Sin cache: la vista es "viva" (el usuario crea campanas y quiere
        // verlas reflejadas al volver). Si en el futuro pesa, ponemos cache
        // de 30s por (user, app, fechaInicio, fechaFin).
        $resp = $this->api->post('Notify/listarCampanas', $payload) ?? [];

        return response()->json([
            'data' => $resp['Campanas'] ?? [],
            'meta' => [
                'app_core' => $appCore,
                'pais'     => Session::get('Pais'),
            ],
        ]);
    }

    /**
     * Metricas de entrega de una campana (o send puntual). Proxy de
     * /Notify/metricasCampana en HMSrvAuth.
     */
    public function metricas(Request $request): JsonResponse
    {
        $payload = [
            'idCampaign' => $request->input('idCampaign') ?: null,
            'idSend'     => $request->input('idSend')     ?: null,
        ];

        $resp = $this->api->post('Notify/metricasCampana', $payload) ?? [];

        return response()->json([
            'metricas' => $resp['Metricas'] ?? [],
        ]);
    }

    /**
     * Drill-down paginado por dispositivo de una campana. Proxy de
     * /Notify/detalleEntregaCampana en HMSrvAuth.
     */
    public function detalle(Request $request): JsonResponse
    {
        $payload = [
            'idCampaign' => $request->input('idCampaign') ?: null,
            'idSend'     => $request->input('idSend')     ?: null,
            'estadoPush' => $request->input('estadoPush') ?: null,
            'page'       => (int) ($request->input('page')     ?: 1),
            'pageSize'   => (int) ($request->input('pageSize') ?: 50),
        ];

        $resp = $this->api->post('Notify/detalleEntregaCampana', $payload) ?? [];

        return response()->json([
            'data'     => $resp['Detalle']  ?? [],
            'total'    => $resp['Total']    ?? 0,
            'page'     => $resp['Page']     ?? $payload['page'],
            'pageSize' => $resp['PageSize'] ?? $payload['pageSize'],
        ]);
    }

    /**
     * Construye el payload comun del dashboard (filtros de periodo + app).
     * Defaults: ultimos 30 dias.
     *
     * idCampaigns: opcional, array de IDs. Lo enviamos como CSV al SP para
     * no depender de TVPs (los SPs de dashboard usan split de VARCHAR).
     */
    private function dashboardPayload(Request $request): array
    {
        $fechaFin    = $request->input('fechaFin')    ?: date('Y-m-d');
        $fechaInicio = $request->input('fechaInicio') ?: date('Y-m-d', strtotime('-29 days'));

        $ids = $request->input('idCampaigns', []);
        if (!is_array($ids)) $ids = array_filter(explode(',', (string) $ids));
        $ids = array_values(array_filter(array_map('intval', $ids), fn ($v) => $v > 0));

        return [
            'app'         => Session::get('AppNotify.idLocal'),
            'fechaInicio' => $fechaInicio,
            'fechaFin'    => $fechaFin,
            'idCampaigns' => $ids ? implode(',', $ids) : null,
        ];
    }

    /**
     * Resumen: 1 fila de KPIs agregados del periodo.
     * Proxy de /Notify/dashboardResumen en HMSrvAuth.
     */
    public function dashboardResumen(Request $request): JsonResponse
    {
        $resp = $this->api->post('Notify/dashboardResumen', $this->dashboardPayload($request)) ?? [];

        return response()->json([
            'resumen' => $resp['Resumen'] ?? [],
        ]);
    }

    /**
     * Serie temporal diaria (1 fila por dia) para el grafico de linea.
     */
    public function dashboardSerie(Request $request): JsonResponse
    {
        $resp = $this->api->post('Notify/dashboardSerieDiaria', $this->dashboardPayload($request)) ?? [];

        return response()->json([
            'serie' => $resp['Serie'] ?? [],
        ]);
    }

    /**
     * Top N campanas por entregadas para el ranking de barras.
     */
    public function dashboardTop(Request $request): JsonResponse
    {
        $payload         = $this->dashboardPayload($request);
        $payload['top']  = (int) ($request->input('top') ?: 5);

        $resp = $this->api->post('Notify/dashboardTopCampanas', $payload) ?? [];

        return response()->json([
            'top' => $resp['Top'] ?? [],
        ]);
    }

    /**
     * @deprecated KPIs v1 basados en listarCampanas (sin rangos reales).
     * Mantenido temporalmente por si hay cache de vistas; usar
     * dashboardResumen() en su lugar.
     */
    public function kpis(): JsonResponse
    {
        $appCore = Session::get('AppNotify.idLocal');

        $resp  = $this->api->post('Notify/listarCampanas', ['app' => $appCore]) ?? [];
        $rows  = $resp['Campanas'] ?? [];

        $total       = count($rows);
        $enEjecucion = 0;
        $programadas = 0;
        $completadas = 0;
        $ultimaFecha = null;

        foreach ($rows as $r) {
            $estado = $r['Estado'] ?? '';
            if ($estado === 'EnEjecucion') $enEjecucion++;
            elseif ($estado === 'Programada') $programadas++;
            elseif ($estado === 'Completada') $completadas++;

            $fechaStr = $r['UltimaActualizacion'] ?? $r['CreatedAt'] ?? null;
            if ($fechaStr && (!$ultimaFecha || $fechaStr > $ultimaFecha)) {
                $ultimaFecha = $fechaStr;
            }
        }

        return response()->json([
            'total'        => $total,
            'enEjecucion'  => $enEjecucion,
            'programadas'  => $programadas,
            'completadas'  => $completadas,
            'ultimaFecha'  => $ultimaFecha,
        ]);
    }

    /* ---------------------------------------------------------------
     * Lifecycle (proxy a HMSrvAuth)
     * -------------------------------------------------------------*/

    private function lifecycle(Request $request, string $endpoint): JsonResponse
    {
        $idCampaign = (int) $request->input('idCampaign', 0);
        if ($idCampaign <= 0) {
            return response()->json(['Error' => 1, 'Message' => 'idCampaign invalido'], 422);
        }
        $resp = $this->api->post('Notify/' . $endpoint, ['idCampaign' => $idCampaign]) ?? [];
        return response()->json($resp);
    }

    public function pausar  (Request $r): JsonResponse { return $this->lifecycle($r, 'pausarCampana');   }
    public function reanudar(Request $r): JsonResponse { return $this->lifecycle($r, 'reanudarCampana'); }
    public function detener (Request $r): JsonResponse { return $this->lifecycle($r, 'detenerCampana');  }
    public function eliminar(Request $r): JsonResponse { return $this->lifecycle($r, 'eliminarCampana'); }
}
