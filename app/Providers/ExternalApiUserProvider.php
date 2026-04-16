<?php

namespace App\Providers;

use App\Exceptions\InvalidCredentialsException;
use Exception;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ExternalApiUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // This method is called from subsequent calls until the session expires.
        //
        // As you don't have a local users database we are going
        // to assume the identifier saved into the session is fine.
        //
        // Session cookies are encrypted by default
        //
        // This avoid calling the external service on every navigation.
        //
        // The downside is that if the user is not authorized anymore
        // in the external service, you won't know until their session expires.
        //
        // Ideally you should set a lower session duration so user
        // gets logged out quickier.
        //
        // An alternative is to save encrypted the user's credentials
        // and call the external service every time.
        //
        // But that would make a external API call on every request,
        // making your app slower. But is the most secure way.
        //
        // If you want I can make an modified version exemplifying 
        // how you could do this.
        return new GenericUser([
            'id' => $identifier,
            'email' => $identifier,
            'remember_token' => $identifier,
        ]);
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (! array_key_exists('username', $credentials)) {
            return null;
        }
        if ($credentials['remember_me']) {
            Config::set('session.lifetime', 60 * 24 * 365 * 2); // 2 años
        }
        // GenericUser is a class from Laravel Auth System
        return new GenericUser([
            'id' => $credentials['username'],
            'email' => $credentials['username'],
            'password' => Hash::make( $credentials['password']),
            'remember_token' => $credentials['remember_me'],
            'country'=> $credentials['country']
        ]);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (! array_key_exists('password', $credentials)) {
            return false;
        }
        // This is a simplified usage of Laravel's HTTP Client to call the external API
        // You might need to send more info to the external service.
        // Please refer to the HTTP Client docs to learn how to use it properly.
        //$response = Http::post(config('global.URL_HM').'GestorFlotas/'.$credentials['country'].'/loginauth', [
            $response = Http::post(config('global.URL_HM').'AM/'.$credentials['country'].'/verifyAuth', [
            'usuario' => $user->email,
            'clave' => $credentials['password'],
            //'app' => $credentials['app'],
            'idAplicacion' => $credentials['app'],
            'ip' => getIPAddress(),
            'appNotify' => $credentials['appNotify'],
        ]);
        
        if($response['Error']){
            if(isset($response['Mensaje'])){
               
                $errorcode =isset($response['CodeError'])?$response['CodeError']:0;
                $link =isset($response['Datos']['Link'])?$response['Datos']['Link']:null;
                $title =isset($response['Title'])?$response['Title']:null;
                //throw new InvalidCredentialsException($response['Mensaje'],$errorcode);
                throw new InvalidCredentialsException($response['Mensaje'],$errorcode,$link,$title);
            }
        }
        if ($response->ok()){
            
            Cache::forget('listVeh_'.Session::get('UserHM'));
            if (validVarArray($response, 'CodeError') && $response['CodeError'] === -90) {
                $ProxCad = [
                     "Message" => 'Tus credenciales de inicio de sesión están próximos a caducar',
                     "ExpLink" => $response['Datos']['ExpLink'],
                     "Link" => $response['Datos']['Link'],
                     "Show" => true
                 ];
                 Session::put('ProxCad', $ProxCad);
             }
            Session::put('UCode', $response['Datos']['UCode']);
            Session::put('Uid', $response['Datos']['Uid']);
            Session::put('FName', $response['Datos']['FName']??'');
            Session::put('Pais', $credentials['country']);
            Session::put('Perfil',isset($response['Datos']['Perfil'][0])?$response['Datos']['Perfil'][0]:'0');
            Session::put('Conce', isset($response['Datos']['Conce'])?$response['Datos']['Conce']:false);
            Session::put('lastAuthCheckAt', now());
            $this->initCheckFilters();
            
        } 

        return $response->ok(); 
    }
    function initCheckFilters(){
        $checks = [
            'Activo' => ['chkAlias' => true,'chkTag' => true,'chkIgn' => true,'chkMarca' => true],
            'Gps' => ['chkVBat' => true,'chkVAli' => true,'chkGps' => true,'chkRumbo' => true,'chkVel' => true],
            'Comandos' => ['chkCTrack' => true,'chkCSUbi' => true,'chkCComm' => true,'COptions' => true],
            'Otros' => ['chkDir'=>true, 'chkFHUlt' => true],
        ];
        Session::put('PrefCheck', $checks);

        $filters = [
            'Ign' => ['chkIgnOn' => true,'chkIgnOff' => true,'chkIgnNull' => true],
            'Gps' => ['chkGpsOn' => true,'chkGpsOff' => true],
            'Grupo' => 'sinAgrupar',
        ];
        Session::put('PrefFilter', $filters);
    }
}
