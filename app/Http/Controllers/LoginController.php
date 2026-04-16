<?php

namespace App\Http\Controllers;


use App\Exceptions\InvalidCredentialsException;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Stevebauman\Location\Facades\Location;
use App\Support\Tenant;

class LoginController extends Controller
{
    public function __invoke(){
        $countries=['EC','PE','CO','CH','MX','PA'];
        $ip = request()->ip();
        
        /*$data = Location::get($ip);
        $countryCode = (!isset($data->countryCode) || empty($data->countryCode)) ? 'EC' : (in_array($data->countryCode, $countries)?$data->countryCode:'EC');*/
      
    return view('auth.login',['countryCode' => /*$countryCode*/'EC']);
    }

    public function login(LoginRequest $req){
        try {
            $credentials = $req->getCredentials();
            $remenber = $req->input('remember_me') ?true:false;
            if (Auth::attempt($credentials, $remenber)) {
                // Autenticación exitosa
                Config::set('UserHM', $credentials['username']);
                Config::set('PassHM', encryptAES($credentials['password']));
                Session::put('UserHM', $credentials['username']);
                Session::put('PassHM', encryptAES($credentials['password']));
                Session::put('AppHM', 59);
                Session::put('AppNotify', $credentials['appNotify']);
                Session::put('AppNAME', getCodeNameApp($credentials['appNotify'],env('APPS')));
                //dd($credentials['appNotify'],env('APPS'),getCodeNameApp($credentials['appNotify'],env('APPS')));
                
                //getCacheVehList();
                //return redirect('/mapa');
                /*if($credentials['appNotify']==27){
                    return redirect('/Message/'.Session::get('Pais').'/'.Session::get('AppNAME').'/notification');
                }else{
                    return redirect('/Message/'.Session::get('Pais').'/'.Session::get('AppNAME').'/create');
                }*/
                // ---- Cambiar conexión inmediatamente después de autenticar
               
                Tenant::setCountryConnection($credentials['country']);
                
                return redirect('/Message/'.Session::get('Pais').'/'.Session::get('AppNAME').'/notification');
                
            }
        } catch (InvalidCredentialsException $e) {
             if ($e->getCode() == -12 || $e->getCode() == -6 || $e->getCode() == -7) {
                return redirect()->route('login')->with([
                    'ErrorRedirectLink' => $e->getLink(), 
                    'ErrorTitle' => is_null($e->getTitle())?'Ingreso Fallido':$e->getTitle(),
                    'ErrorText' => $e->getMessage(),
                    'ErrorIcon' => $e->getCode() == -90?'warning':'error',
                    //'username' => $e->errorMessage()
                ]);
            }
            return redirect()->back()->withErrors(['username' => $e->errorMessage()]); 
        }
        return redirect()->back()->withErrors(['username' => 'Ocurrió un error al intentar autenticarse.']);
    }

    public function logout(Request $req)
    {
        // Limpiar la sesión
        $req->session()->flush();
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        Cache::flush();
        Session::flush();
        config()->set('UserHM', null);
        config()->set('PassHM', null);
        return redirect()->guest(route("login"));
    }

    public function deleteAllVar()
    {
        session()->flush();
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        Cache::flush();
        Session::flush();
        config()->set('UserHM', null);
        config()->set('PassHM', null);
    }


  
}
