<?php

namespace App\Http\Controllers;

use App\Support\ReenviaIdentidadCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * AvisosController (HX-29, D4)
 * Alimenta la campana del encabezado: pide al SSO
 *   GET {base_uri}/api/{Pais}/AuthSSO/avisos?app=Notify
 * con el Bearer de la sesion y devuelve {Error, Avisos[]}. La campana nunca debe
 * romper la pagina: ante cualquier fallo responde 200 {Error:true, Avisos:[]}.
 */
class AvisosController extends Controller
{
    use ReenviaIdentidadCliente;

    public function index(Request $request): JsonResponse
    {
        $vacio = ['Error' => true, 'Avisos' => []];

        try {
            $url = rtrim((string) config('services.core_sso.base_uri'), '/')
                . '/api/' . strtoupper((string) Session::get('Pais', 'EC')) . '/AuthSSO/avisos';

            $res = Http::withToken((string) Session::get('access_token'))
                ->withHeaders($this->cabecerasCliente())
                ->acceptJson()
                ->timeout(8)
                ->connectTimeout(5)
                ->get($url, ['app' => 'Notify']);

            $json = $res->json();
            if (!$res->ok() || !is_array($json) || !isset($json['Avisos']) || !is_array($json['Avisos'])) {
                return response()->json($vacio);
            }

            return response()->json(['Error' => (bool) ($json['Error'] ?? false), 'Avisos' => array_values($json['Avisos'])]);
        } catch (\Throwable $e) {
            Log::warning('AvisosController: no se pudieron pedir los avisos', ['message' => $e->getMessage()]);
            return response()->json($vacio);
        }
    }
}
