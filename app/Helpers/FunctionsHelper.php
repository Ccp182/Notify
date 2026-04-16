<?php

use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use function PHPUnit\Framework\isEmpty;
date_default_timezone_set('America/Guayaquil');

function callApi($url,$parameters) {
    $response = Http::post($url, $parameters);
     if ($response->ok()) {
        //if(!$response['Error'])
            $data = $response->json();
        return $data;
    } return null;
}

function getParamsSplit($stringsSplit){
    $url_components = parse_url($stringsSplit);
    parse_str($url_components['path'], $params);
    return $params;
}

    function getIPAddress() {   
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
            $ip = $_SERVER['HTTP_CLIENT_IP'];  
        }  
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
        }  
        else{  
            $ip = $_SERVER['REMOTE_ADDR'];  
        }  
        return $ip;  
    }

    function truncate(string $text, int $length = 20): string {
        if (strlen($text) <= $length) {
            return $text;
        }
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, " "));
        $text .= "...";
        return $text;
    }

    function setDefaultValue($array,$campo,$default){
        if(isset($array[''.$campo.''])){
            if(!is_null([''.$campo.''])){
                return $array[''.$campo.''];
            }
        }
        return $default;
    }

    function setDefaultValueVarString($array,$campo){
        return setDefaultValue($array,$campo,'');
    }


    function validVarArray($row,$campo){
        if(isset($row[$campo]))
            if(!is_null($row[$campo]))
                if(strlen($row[$campo])>0)
                        return true;
        
        return false;
    }

    function validVarArrayComplex($row,$campo){
        if(isset($row[$campo]))
            if(!is_null($row[$campo]))
                if(!empty($row[$campo]))
                        return true;
        
        return false;
    }

    function setVarArray($itemVehiculo,$row,$campo,$campoNew){
        unset($itemVehiculo[$campoNew]);
        if(validVarArray($row, $campo))
            $itemVehiculo[$campoNew]=$row[$campo];
        return $itemVehiculo;
    }

    function setVarArrayInt($itemVehiculo,$row,$campo,$campoNew){
        unset($itemVehiculo[$campoNew]);
        if(validVarArray($row, $campo))
            $itemVehiculo[$campoNew]=(int)$row[$campo];
        return $itemVehiculo;
    }

    function setVarArrayDouble($itemVehiculo,$row,$campo,$campoNew){
        unset($itemVehiculo[$campoNew]);
        if(validVarArray($row, $campo))
            $itemVehiculo[$campoNew]=(double)$row[$campo];
        return $itemVehiculo;
    }

    function setVarArrayBoolean($itemVehiculo,$row,$campo,$campoNew){
        unset($itemVehiculo[$campoNew]);
        if(validVarArray($row, $campo))
            $itemVehiculo[$campoNew]=((int)$row[$campo]=1)?true:false;
        return $itemVehiculo;
    }

    function setVarArrayCustom($itemVehiculo,$campoNew,$value){
        unset($itemVehiculo[$campoNew]);
        if(!is_null($value))
                if(strlen($value)>0)
                    if($value!='null')
                        $itemVehiculo[$campoNew]=$value;
        return $itemVehiculo;
    }

    function setVarArrayParentCustom($itemVehiculo,$campoParentNew,$campoNew,$value){
        unset($itemVehiculo[$campoParentNew][$campoNew]);
        if(!is_null($value))
                if(strlen($value)>0)
                    if($value!='null')
                        $itemVehiculo[$campoParentNew][$campoNew]=$value;
        return $itemVehiculo;
    }

    function setVarArrayParent($itemVehiculo,$row,$campo,$campoParentNew,$campoNew){
        unset($itemVehiculo[$campoParentNew][$campoNew]);
        if(validVarArray($row, $campo))
            $itemVehiculo[$campoParentNew][$campoNew]=$row[$campo];
        return $itemVehiculo;
    }

    function getValidVarArray($row,$campo){
        if(validVarArray($row, $campo))
            return $row[$campo];
        return null;
    }

    function existAndNotNUll($array,$campo){
        if(isset($array[''.$campo.''])){
            if(!is_null([''.$campo.''])){
                return TRUE;
            }
        }
        return FALSE;
    }

    function getArrayFromObject($object){
        return json_decode(json_encode($object), true);
    }

    function existAndNotNUllAndNotEmpty($array,$campo){
        if(isset($array[''.$campo.''])){
            if(!is_null([''.$campo.''])){
                if(!empty($array[''.$campo.''])){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    function getDateFormat($fecha,$formatstart='Y-m-d H:i:s',$format='Y-m-d H:i:s'){
        $date = DateTime::createFromFormat($formatstart, $fecha);
        $date = $date->format($format);
        return $date;
    }

    function getDateFormatCarbon($fecha,$formatstart='Y-m-d H:i:s',$format='Y-m-d H:i:s'){
        $date = Carbon::createFromFormat($formatstart, $fecha)->format($format);
        return $date;
    }

    function getClassAlert($semaforo){
        $class='alert-success';
        switch($semaforo){
            case "1":
                $class='danger';
                break;
            case "2":
                $class='warning';
                break;
            case "3":
                $class='success';
                break;
        }
        return $class;
    }

    function verificarRuta($ruta, $urlPorDefecto) {
        if (file_exists($ruta)) {
            return $ruta;
        } else {
            return $urlPorDefecto;
        }
    }

    function encryptAES($str) {
        $ciphertextHexStr = AesCipher::encryptHexStr($str);
        return $ciphertextHexStr;
    }
      
    function decryptAES($str) {
        $plaintextHexStr = AesCipher::decryptHexStr($str);
        return $plaintextHexStr;
    }

    function getVehList(){
        $listVeh = fetchAndCacheListPut(true);
        return $listVeh;
    }

    function getCacheVehList(){
        $listVeh = /*Cache::get('listVeh_'.Session::get('UserHM'));
        if ($listVeh === null || empty($listVeh)) $listVeh =*/ fetchAndCacheListPut(true);
        return $listVeh;
    }

    /*function getCacheVehTableList(){
        $listVehTable = Cache::get('listVehTable_'.Session::get('UserHM'));
        if ($listVehTable === null || empty($listVehTable)) $listVehTable = fetchAndCacheListPut(false);
        return $listVehTable;
    }*/

    function getCacheVehListAlt(){
        $listVehAlt = Cache::get('listVehAlt_'.Session::get('UserHM'));
        if ($listVehAlt === null || empty($listVehAlt)) $listVehAlt = fetchAndCacheListPut(false);
        return $listVehAlt;
    }

    function fetchAndCacheListPut($complete){
        
        $parameters = array(
            'idUsuario' =>Session::get('UCode'),
            'ultimaConsulta' => isset($req['LastTime'])?$req['LastTime']:''
        );
        $array_json = callApi(config('global.urlhm').Session::get('Pais').'/getVehByUser2',$parameters);
        $arrayResponse["Data"]=array();
        $listAlt = [];
        $listDatatable = [];
        $sumOnAct=0;$sumOffAct=0;$sumNullAct=0;
        if($array_json!=null){
            foreach($array_json['Vehiculos'] as $row){
                /*$listAlt[$row['Vid']] =array(
                    'Vid' => $row['Vid'],
                    'Alias' => $row['Alias'],
                    'Marca' => isset($row['Marca'])?$row['Marca']:null,
                    'Model' => isset($row['Model'])?$row['Model']:null,
                    'IdAct' => $row['IdAct']
                );*/
                $row['Placa'] = $row['Alias'];
                $row['Marca'] = validVarArray($row,'Marca')?trim($row['Marca']):'NO DEFINIDO';
                //$row['Alias'] = View::make('others.activoDatatable', ['item' => $row])->render();
                //$row['Acciones'] = View::make('others.activoAccionesDatatable', ['item' => $row])->render();
                $listAlt[$row['Vid']] = $row;
                //$listDatatable[$row['Vid']] =array($row['Vid'],$row['Placa'],$row['Alias'],$row['Acciones']);
                //Vehiculo::save($row);
                $row['Ign']==1?$sumOnAct++:($row['Ign']==0?$sumOffAct++:$sumNullAct++);
                array_push($arrayResponse["Data"], $row);
               // array_push($arrayResponse["Table"], $listDatatable);
            }
        }
       
        Cache::put('lastTime_'.Session::get('UserHM'), getCurrentFormattedDateTime(), 1800); 
        //dd(Cache::get('lastTime_'.Session::get('UserHM')));
        if(count($arrayResponse["Data"])>0){
            //Cache::put('listVeh_'.Session::get('UserHM'), $listAlt, 1800); 
            Cache::put('listVeh_'.Session::get('UserHM'), /*json_encode(*/$listAlt/*)*/, 1800); 
            
            //Cache::put('listVehAlt_'.Session::get('UserHM'), $listAlt, 1800); 
            //Cache::put('listVehTable_'.Session::get('UserHM'), $listDatatable, 1800); 
        }
        Cache::put('kpi_'.Session::get('UserHM'),['Ign'=>['on' => $sumOnAct,'off' => $sumOffAct,'null' => $sumNullAct]]);
        unset($array_json,$arrayResponse,$listDatatable,$listAlt);
        //return Cache::get(($complete?'listVeh_':'listVehTable_').Session::get('UserHM'));
        return Cache::get('listVeh_'.Session::get('UserHM'));
    }

    function getCurrentFormattedDateTime(){
        date_default_timezone_set('America/Guayaquil');
        return date('Ymd H:i:s');
    }

    function convertStringsToBooleans($data) {
        array_walk_recursive($data, function(&$value) {
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            }
        });
        return $data;
    }

    function getCodeNameApp($number,$apps) {
        $apps = json_decode($apps, true);
        if ($apps !== null) {
            foreach ($apps as $key => $value) {
                if ($value == $number) {
                    return $key;
                }
            }
        }
        return null;
    }

    function getCustomDataByApp($name){
        $overview=[];
        $overview['CSS']='https://am.24hm.net/assets/css/main_'.env('NAME_'.$name.'_CSS').'.css';
        $overview['COLOR']=env('COLOR_'.$name);
        $overview['LOGO']=env('LOGO_'.$name);
        $overview['FAVICON']=env('FAVICON_'.$name);
        $overview['LOGO_WHITE']=env('LOGO_'.$name.'_WHITE');
        return $overview;
    }

    
?>