<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureAppSelected
 * -----------------------------------------------------------------------------
 * Opcion B de la migracion HMNotify a SSO: el combo de seleccion de app
 * del login legacy ahora vive en una pantalla post-login (/select-app)
 * protegida por este middleware.
 *
 * Si el usuario autenticado NO tiene Session::AppNotify:
 *   - Redirige a /select-app.
 *   - Esa ruta a su vez no pasa por este middleware (para evitar loop).
 *
 * Si tiene solo 1 app autorizada, AppSelectController hace auto-seleccion.
 */
class EnsureAppSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('AppNotify')) {
            return redirect()->route('select-app.show');
        }

        return $next($request);
    }
}
