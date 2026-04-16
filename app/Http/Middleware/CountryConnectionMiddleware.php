<?php
// app/Http/Middleware/CountryConnectionMiddleware.php
namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Support\Facades\Session;

class CountryConnectionMiddleware
{
    public function handle($request, Closure $next)
    {
        $country = Session::get('Pais', 'EC'); // default EC si no hay sesión
        Tenant::setCountryConnection($country);
       
        return $next($request);
    }
}
