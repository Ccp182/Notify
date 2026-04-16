<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
class DomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->setConfig('EC');
        $countries=['EC','PE','CO','CH','MX','PA'];
        $host =  parse_url(request()->root())['host'];
        $full =  URL::full();
        if(str_contains($host, '-')){
            foreach($countries as $country){
                if (str_contains($host, "-".strtolower($country))){
                    $this->setConfig($country);
                    break;
                }
            }
        }else{
            foreach($countries as $country){
                if (str_contains($full, '/'.$country.'/') || str_contains($full, '_'.$country)){
                    if($country=='EC'){
                        break;
                    }else{
                        $this->setConfig($country);
                        break;
                    }
                }
            }
        }
        DB::reconnect();
        return $next($request);
    }
    public function setConfig($country){
        if($country=='EC'){
            Config::set("database.default", "sqlsrv");
            Config::set('PaisSuf', '');
            Config::set('Pais', 'EC');
        }else{
            Config::set("database.default", "sqlsrv".strtolower($country));
            Config::set('PaisSuf', '_'.$country);
            Config::set('Pais', $country);
        }
         dd([
                    'connection' => config('database.default'),
                    'paisSuf'    => config('PaisSuf'),
                    'pais'       => config('Pais')]);
    }
}
