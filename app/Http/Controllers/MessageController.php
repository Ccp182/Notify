<?php

namespace App\Http\Controllers;

use App\Support\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
class MessageController extends Controller
{
    public function __invoke(){
        $overview=array();
        $overview['CurrentDate']=Carbon::now()->format('Y-m-d');
        $overview['BeforeDate']=Carbon::now()->subDays(7)->format('Y-m-d');
        return view('message',['overview' => $overview]);
    }


    public function create($country, $app_name){
        $overview=array();
        $overview['CurrentDate']=Carbon::now()->format('Y-m-d');
        $overview['BeforeDate']=Carbon::now()->subDays(7)->format('Y-m-d');
        $overview=getCustomDataByApp(Session::get('AppNAME'));
        $overview['postDispositivos']=env('BASE_POST').'getDispositivos';
        $overview['postDispositivosSend']=env('BASE_POST').'postDispositivosSend';
        $overview['postDispoTemplateSendNew']=env('BASE_POST').'postDispoTemplateSendNew';
        $overview['postDispoTemplateNumSendNew']=env('BASE_POST').'postDispoTemplateNumSendNew';
         return view('message',['overview' => $overview]);
    }
    
    

    public function getDispositivos(){
        $parameters = array(
            'app' =>Session::get('AppNotify')
        );
        
        $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getDispoByApp',$parameters);
        $arrayResponse["data"]=[];

        if($array_json!=null){
            //$arrayResponse["data"]=$array_json['Dispositivos'];
            foreach($array_json['Dispositivos'] as $item){
                $itemarray=array();
                $itemarray=$item;
                $itemarray['App']=$parameters['app'];
                /*$itemarray['Check'] = '<input type="checkbox" data-rowid="' .$item['DId']. '">';*/
                $arrayResponse["data"][]=$itemarray;
                unset($itemarray);
            }
        }
        unset($array_json);
        return $arrayResponse;
    }

    public function getDispositivosAlt(Request $req){

        if($req['template']=='TEMPLATE'){
            if(validVarArrayComplex($req,'numList')){
                $parameters = array(
                    'idAplicacion' =>Session::get('AppNotify'),
                    'numList' => $req['numList'],
                );
                $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getDispoByNumero',$parameters);
            }else{
                $parameters = array(
                    'idAplicacion' =>Session::get('AppNotify'),
                    'chasisList' => $req['chasisList'],
                    'motorList' => $req['motorList']
                );
                $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getDispoByMotorChasis',$parameters);
            }
        }else{
            $parameters = array(
                'app' =>Session::get('AppNotify'),
                'idSubGrupo' => validVarArrayComplex($req,'idSubGrupo')?implode(',', $req['idSubGrupo']):null,
                'filtroTipoUser' => $req['filtroTipoUser'],
                'idTipoEnt' => validVarArrayComplex($req,'idTipoEnt')?implode(',', $req['idTipoEnt']):null,
                'plataforma' => $req['plat']=='TODOS'?NULL:$req['plat']
            );
            $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getDispoUserByApp',$parameters);
        }
        $arrayResponse["data"]=[];
        if($array_json!=null && isset($array_json['data']) && is_array($array_json['data'])) {
            $count=0;
            foreach($array_json['data'] as $item){
                $itemarray=array();
                //$itemarray=$item;
                $itemarray['Check']='<div class="form-check"><input type="checkbox" class="form-check-input" id="ordercheck'.$count.'"></div>';
                $itemarray['App']=$req['template']=='TEMPLATE'? $parameters['idAplicacion']:$parameters['app'];
                $itemarray['Name']=validVarArray($item,'Name')?$item['Name']:'--';
                $itemarray['NameExt']=[(validVarArray($item,'Name')?$item['Name']:'--'),(validVarArray($item,'IdSubUser')?'SUBUSUARIO':'USUARIO')];
                $itemarray['Min']=validVarArray($item,'Min')?$item['Min']:'--';
                $itemarray['Plat']=validVarArray($item,'Plat')?$item['Plat']:'--';
                $itemarray['MDisp']=validVarArray($item,'MDisp')?$item['MDisp']:'--';
                $itemarray['Tipo']=validVarArray($item,'Tipo')?$item['Tipo']:'--';
                $itemarray['DId']=validVarArray($item,'DId')?$item['DId']:'--';
                $itemarray['NGroup']=validVarArray($item,'NGroup')?$item['NGroup']:'--';
                /*$itemarray['Check'] = '<input type="checkbox" data-rowid="' .$item['DId']. '">';*/
                $arrayResponse["data"][]=$itemarray;
                $count++;
                unset($itemarray);
            }
        }
        unset($array_json);
        return $arrayResponse;
    }
    

    public function postDispositivosSend(Request $request){
        $error=false; $messageError='Datos recibidos correctamente y enviados a procesar';
        $title = $request->input('title-input');
        $subtitle = $request->input('subtitle-input');
        $mensaje = $request->input('mensaje-input');
        $url = $request->input('url-input');
        //$date = $request->input('date-input');
        $ws = $request->input('ws-input');
        $nivel = $request->input('selectNivelNotification');
        $contacto = $request->input('contacto-input');
        $tipoAlerta = $request->input('selectTipoNotification');
        $tipoAlertaName='Multimedia';
        $dispositivosInput = $request->input('dispositivos-input');
        $tipo=2; $subtipo=6;
        $mensajexmpp=null;
        $rutaCompleta=null;
       
        if ($request->hasFile('customFileNotification')) {
            $imagen = $request->file('customFileNotification');
            /*$imagenNombre = time() . '_' . $imagen->getClientOriginalName();
            $ruta = $imagen->move(public_path('assets/upload'), $imagenNombre);
            if ($ruta) {
                //$rutaCompleta = public_path('assets/upload' . $imagenNombre);
                $rutaCompleta='https://notify.24hm.net/assets/upload/' . $imagenNombre;
                $error=false;
            }else{
                $error=true;
                $messageError='No s epudo procesar la imagen correctamente, por favor intente más tarde';
            }*/
            $extensionesPermitidas = ['png', 'jpg', 'jpeg'];
            $extension = $imagen->getClientOriginalExtension();
            
            if (!in_array($extension, $extensionesPermitidas)) {
                // La extensión del archivo no es válida
                $error = true;
                $messageError = 'El archivo debe ser PNG o JPG.';
            }
            
            // Validar el tamaño del archivo
            $tamanoMaximo = 2 * 1024 * 1024; // 2 MB
            
            if ($imagen->getSize() > $tamanoMaximo) {
                // El tamaño del archivo excede el límite
                $error = true;
                $messageError = 'El archivo debe ser de máximo 2 MB.';
            }
            // Si no hay errores de validación, procede a mover y guardar la imagenS
            if (!$error) {
                $imagenNombre = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->move(public_path('assets/upload'), $imagenNombre);
                if ($ruta) {
                    $rutaCompleta = 'https://notify.24hm.net/assets/upload/' . $imagenNombre;
                } else {
                    $error = true;
                    $messageError = 'No se pudo procesar la imagen correctamente, por favor intente más tarde.';
                }
            }
           
            
        }
        $dispositivosData = json_decode($dispositivosInput, true);
        $arrayInsert[]=null;
        
        if(!$error){
            switch ($tipoAlerta) {
                case 0:
                    // Alerta de tipo Multimedia
                    $tipoAlertaName='Multimedia';
                    $tipo=2; $subtipo=6;
                    $mensajexmpp='{"Multimedia":{"Titulo":"'.$title.'","Subtitulo": "'.$subtitle.'","Image":"'.$rutaCompleta.'","Url":"'.$url.'"}}';
                    break;
                case 1:
                    // Alerta de tipo Vencimiento
                    $tipoAlertaName='Vencimiento';
                    $mensajexmpp='{"Multimedia": {"Titulo": "'.$title.'","Subtitulo": "'.$subtitle.'","Nivel": '.$nivel.',"Call": "'.$contacto.'","Ws": "'.$ws.'"}}';
                    $tipo=2; $subtipo=4;
                    break;
                case 2:
                    // Alerta de tipo HTML
                    $tipoAlertaName='HTML';
                    $tipo=2; $subtipo=6;
                    break;
                case 3:
                    // Alerta de tipo TEXTO
                    $tipoAlertaName='TEXTO';
                    $mensajexmpp=$subtitle;
                    $tipo=1; $subtipo=6;
                    break;
                default:
                    $tipo=1; $subtipo=6;
                    $mensajexmpp=$subtitle;
                    // Manejar el caso por defecto según sea necesario
                    break;
            }
    
            
            // Iterar sobre los datos de los dispositivos
            foreach ($dispositivosData as $dispositivo) {
                $arrayInsert[$dispositivo['Min']] = DB::insert("INSERT INTO HMMNotificaciones (Mensaje, Min, UsuarioOrigen, UsuarioDestino, Operadora, Tipo, Subtipo, Id, Vid, FechaHoraOcurrencia, Estado, FechaHoraRegistro, FechaModificacion, Usuario, ActivoEstado, MensajeXMPP) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, DATEADD(HOUR, 5, GETDATE()), ?, ?, ?, ?)", [
                    $title?$title:$subtitle,
                    $dispositivo['Min'],
                    //'0993226730',//quitar esto por la linea anterior 
                    'notify@hmmovil',
                    'default',
                    -1,
                    $tipo,
                    $subtipo,
                    null,
                    'N|' . $dispositivo['DId'].'|'.$dispositivo['App'],
                    'R',
                    null,
                    'notify',
                    'A',
                    $mensajexmpp
                ]);
    
            }
            if(count($arrayInsert)==0){
                $error=true;
                $messageError='Las notificaciones a enviar no fue procesadas correctamente, por favor intente más tarde';
            }else{
                //dd();
                $fechaEnvio = date('Y-d-m H:i:s');
                $inserted = DB::insert("INSERT INTO NotifyLog (Titulo, Subtitulo, MensajeHTML, Url, Whatsapp, Contacto, Imagen, Nivel, TipoAlerta, FechaEnvio, UsuarioTransaccion, MensajeXMPP, JSONDetalle, Ip, App) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $title,
                    $subtitle,
                    $mensaje,
                    $url,
                    $ws,
                    $contacto,
                    isset($rutaCompleta)?$rutaCompleta:null,
                    $nivel,
                    $tipoAlertaName,
                    $fechaEnvio, 
                    Session::get('UserHM'), 
                    $mensajexmpp,
                    $dispositivosInput,
                    getIPAddress(),
                    Session::get('AppNotify')
                ]);
            }
        }
        
        return  response()->json(['error' => $error,'message' => $messageError, 'arrayInsert'=>$arrayInsert]);
    }

    public function notificationWizard($country, $app_name){
        $overview=array();
        $overview['CurrentDate']=Carbon::now()->format('Y-m-d');
        $overview['BeforeDate']=Carbon::now()->subDays(7)->format('Y-m-d');
        $overview=getCustomDataByApp(Session::get('AppNAME'));
        $overview['postDispositivos']=env('BASE_POST').'getDispositivosAlt';
        $overview['postDispositivosSendNew']=env('BASE_POST').'postDispositivosSendNew';
        $overview['postDispoTemplateSendNew']=env('BASE_POST').'postDispoTemplateSendNew';
        $overview['postDispoTemplateNumSendNew']=env('BASE_POST').'postDispoTemplateNumSendNew';
        $overview['listGroups'] = $this->getListGroupSub();
        $overview['listTipoEntidad'] = $this->getListTipoEnt();
        return view('notificationWizard',['overview' => $overview]);
    }
   
    function getListGroupSub(){
        $listGroups = Cache::get('listGroups_'.Session::get('UserHM'));
        if ($listGroups === null || empty($listGroups)) $listGroups = $this->fetchAndCacheListGroupSubPut();
        return $listGroups;
    }

    function fetchAndCacheListGroupSubPut(){
        $parameters = array("app" =>Session::get('AppNotify'));
        $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getGrupoSubUsuario',$parameters);
        
        $arrayResponse["Data"]=array();
        if($array_json!=null){
            foreach($array_json['Data'] as $row){
                array_push($arrayResponse["Data"], $row);
            }
        }
        if(count($arrayResponse["Data"])>0){
            Cache::put('listGroups_'.Session::get('UserHM'), $arrayResponse["Data"], 1800);    
        }
        return Cache::get('listGroups_'.Session::get('UserHM'));
        
    }

    function getListTipoEnt(){
        $listTipoEnt = Cache::get('listTipoEnt_'.Session::get('UserHM'));
        if ($listTipoEnt === null || empty($listTipoEnt)) $listTipoEnt = $this->fetchAndCacheListTipoEntPut();
        return $listTipoEnt;
    }

    function fetchAndCacheListTipoEntPut(){
        $parameters = array("app" =>Session::get('AppNotify'));
        $array_json = callApi(config('global.URL_HM').'Notify/'.Session::get('Pais').'/getTipoEntidad',$parameters);
        
        $arrayResponse["Data"]=array();
        if($array_json!=null){
            foreach($array_json['Data'] as $row){
                array_push($arrayResponse["Data"], $row);
            }
        }
        if(count($arrayResponse["Data"])>0){
            Cache::put('listTipoEnt_'.Session::get('UserHM'), $arrayResponse["Data"], 1800);    
        }
        return Cache::get('listTipoEnt_'.Session::get('UserHM'));
        
    }

    public function postDispositivosSendNew(Request $request){
        Tenant::setCountryConnection(Session::get('Pais','EC'));
        $error=false; 
        $messageError='Datos recibidos correctamente y enviados a procesar';
        $tipoAlertaName='TEXTO';
        $mensajexmpp=null;
        $rutaCompleta=null;

        ##FORM1
        $tipoNoti = (int)$request->input('tipoNoti');
        $titleNoti = $request->input('titleNoti');
        $subTitleNoti = $request->input('subTitleNoti');
        
        $tipoMode = (int)$request->input('tipoMode');
        $colorTitleNoti = $request->input('colorTitleNoti');
        $colorSubTitleNoti = $request->input('colorSubTitleNoti');
        $colorFondoNoti = $request->input('colorFondoNoti');
        
        $activateBtnNoti = $request->input('activateBtnNoti');
        $buttonTextNoti = $request->input('buttonTextNoti');
        $colorButtonNoti = $request->input('colorButtonNoti');
        $colorButtonTextNoti = $request->input('colorButtonTextNoti');
        $urlNoti = $request->input('urlNoti');
        $activateBtnNoti=($activateBtnNoti=='on'?TRUE:FALSE);

        ##FORM 3
        $programation_h = $request->input('programation_h');
        
        ##PROXIMAMENTE
        $ws = null;
        $nivel = null;
        $contacto = null;
        $html= $request->input('htmlNoti');
        $rutaHtmlCompleta = null;
        
        if (!empty($html)) {
            $nombreArchivoHtml = 'html_' . time() . '.html';
            $rutaHtml = public_path('assets/upload/HTML/' . $nombreArchivoHtml);
        
            if (file_put_contents($rutaHtml, $html) !== false) {
                $rutaHtmlCompleta = 'https://notify.24hm.net/assets/upload/HTML/' . $nombreArchivoHtml;
            } else {
                $error = true;
                $messageError = 'No se pudo guardar el archivo HTML correctamente.';
            }
        }
        
        if ($request->hasFile('customFileNoti')) {
            $imagen = $request->file('customFileNoti');
            $extensionesPermitidas = ['png', 'jpg', 'jpeg'];
            $extension = $imagen->getClientOriginalExtension();
            
            if (!in_array($extension, $extensionesPermitidas)) {
                $error = true;
                $messageError = 'El archivo debe ser PNG o JPG.';
            }
            
            $tamanoMaximo = 2 * 1024 * 1024; // 2 MB
            
            if ($imagen->getSize() > $tamanoMaximo) {
                $error = true;
                $messageError = 'El archivo debe ser de máximo 2 MB.';
            }
            if (!$error) {
                $imagenNombre = time() . '_' . str_replace(' ', '_', $imagen->getClientOriginalName());
                $ruta = $imagen->move(public_path('assets/upload'), $imagenNombre);
                if ($ruta) {
                    $rutaCompleta = 'https://notify.24hm.net/assets/upload/' . $imagenNombre;
                } else {
                    $error = true;
                    $messageError = 'No se pudo procesar la imagen correctamente, por favor intente más tarde.';
                }
            }
        }
        $tipo=2; $subtipo=6;
        $dispositivosInput = $request->input('dispositivos-input');
        $dispositivosData = json_decode($dispositivosInput, true);
        $arrayInsert[]=null;
        
        if(!$error){
            switch ($tipoNoti) {
                case 0:
                    // Alerta de tipo Multimedia
                    $tipoAlertaName='Informativa';
                    //$mensajexmpp=$subTitleNoti;
                    $mensajexmpp='{"Informativa":{"Titulo":"'.$titleNoti.'","Subtitulo": "'.$subTitleNoti.'","Image":"'.$rutaCompleta.'"}}';
                    $tipo=7; $subtipo=2;
                    break;
                case 1:
                    // Alerta de tipo Multimedia
                    $tipoAlertaName='Multimedia';
                    $tipo=2; $subtipo=6;
                    //$btnJson=$activateBtnNoti?',"BtnUrl":"'.$urlNoti.'","BtnTexto":"'.$buttonTextNoti.'","BtnColor":"'.$colorButtonTextNoti.'","BtnColorF":"'.$colorButtonNoti.'"':'';
                    $btnJson=$activateBtnNoti?',"Btn": ["'.$urlNoti.'","'.$buttonTextNoti.'","'.$colorButtonTextNoti.'","'.$colorButtonNoti.'"]':'';
                    if($colorTitleNoti=='#000000' && $colorSubTitleNoti=='#000000' && $colorFondoNoti=='#ffffff'){
                        $ModelJson=',"Mdl": ['.$tipoMode.']';
                    }else{
                        $ModelJson=',"Mdl": ['.$tipoMode.',"'.$colorTitleNoti.'","'.$colorSubTitleNoti.'","'.$colorFondoNoti.'"]';
                    }
                    
                    $mensajexmpp='{"Multimedia":{"Titulo":"'.$titleNoti.'","Subtitulo": "'.$subTitleNoti.'","Image":"'.$rutaCompleta.'","Url":"'.$urlNoti.'"'.$ModelJson.$btnJson.'}}';
                    break;
                case 2:
                    // Alerta de tipo HTML
                    $tipoAlertaName='HTML';
                    $mensajexmpp='{"Html":{"Titulo":"'.$titleNoti.'","Subtitulo": "'.$subTitleNoti.'","Image":"'.$rutaCompleta.'","Url":"'.$rutaHtmlCompleta.'"}}';
                    $mensajexmpp='{"Html":{"Titulo":"'.$titleNoti.'","Subtitulo": "'.$subTitleNoti.'","Image":"'.$rutaCompleta.'","Url":"'.$rutaHtmlCompleta.'"}}';
                    $tipo=7; $subtipo=2;
                    break;
                case 2:
                    // Alerta de tipo Vencimiento
                    $tipoAlertaName='Vencimiento';
                    //$mensajexmpp='{"Multimedia": {"Titulo": "'.$title.'","Subtitulo": "'.$subtitle.'","Nivel": '.$nivel.',"Call": "'.$contacto.'","Ws": "'.$ws.'"}}';
                    $tipo=2; $subtipo=4;
                    break;
                case 4:
                    // Alerta de tipo TEXTO
                    $tipoAlertaName='Texto';
                    $mensajexmpp=$subTitleNoti;
                    $tipo=1; $subtipo=6;
                    break;
                default:
                    $tipo=1; $subtipo=6;
                    $mensajexmpp=$subTitleNoti;
                    break;
            }
    
           
        
            // Iterar sobre los datos de los dispositivos
            foreach ($dispositivosData as $dispositivo) {
                $arrayInsert[$dispositivo['Min']] = DB::insert("INSERT INTO HMMNotificaciones (Mensaje, Min, UsuarioOrigen, UsuarioDestino, Operadora, Tipo, Subtipo, Id, Vid, FechaHoraOcurrencia, Estado, FechaHoraRegistro, FechaModificacion, Usuario, ActivoEstado, MensajeXMPP) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, DATEADD(HOUR, 5, GETDATE()), ?, ?, ?, ?)", [
                    $titleNoti?$titleNoti:$subTitleNoti,
                    $dispositivo['Min'],
                    //'0993226730',//quitar esto por la linea anterior 
                    'notify@hmmovil',
                    'default',
                    -1,
                    $tipo,
                    $subtipo,
                    null,
                    'N|' . $dispositivo['DId'].'|'.$dispositivo['App'],
                    'R',
                    null,
                    'notify',
                    'A',
                    $mensajexmpp
                ]);
    
            }
            if(count($arrayInsert)==0){
                $error=true;
                $messageError='Las notificaciones a enviar no fue procesadas correctamente, por favor intente más tarde';
            }else{
                //dd();
                $fechaEnvio = date('Y-d-m H:i:s');
                $inserted = DB::insert("INSERT INTO NotifyLog (Titulo, Subtitulo, MensajeHTML, Url, Whatsapp, Contacto, Imagen, Nivel, TipoAlerta, FechaEnvio, UsuarioTransaccion, MensajeXMPP, JSONDetalle, Ip, App) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                    $titleNoti,
                    $subTitleNoti,
                    $html,
                    $urlNoti,
                    $ws,
                    $contacto,
                    isset($rutaCompleta)?$rutaCompleta:null,
                    $nivel,
                    $tipoAlertaName,
                    $fechaEnvio, 
                    Session::get('UserHM'), 
                    $mensajexmpp,
                    $dispositivosInput,
                    getIPAddress(),
                    Session::get('AppNotify')
                ]);
            }
        }
        
        return  response()->json(['error' => $error,'message' => $messageError, 'arrayInsert'=>$arrayInsert]);
    }

    public function postDispoTemplateSendNew(Request $request){
        if (!$request->hasFile('archivo')) {
            return response()->json([
                'error' => true,
                'message' => 'No se envió ningún archivo.'
            ], 400);
        }

        $file = $request->file('archivo');

        // Abrimos el archivo CSV
        $chasisArr = [];
        $motorArr = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ','); // leemos cabeceras
            $chasisIndex = array_search('Chasis', $header);
            $motorIndex = array_search('Motor', $header);

            if ($chasisIndex === false || $motorIndex === false) {
                return response()->json([
                    'error' => true,
                    'message' => 'El archivo debe tener cabeceras: Chasis, Motor.'
                ], 400);
            }

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (!empty($data[$chasisIndex])) {
                    $chasisArr[] = trim($data[$chasisIndex]);
                }
                if (!empty($data[$motorIndex])) {
                    $motorArr[] = trim($data[$motorIndex]);
                }
            }

            fclose($handle);
        }

        $chasis = implode(', ', $chasisArr);
        $motor = implode(', ', $motorArr);

        return response()->json([
            'error' => false,
            'chasis' => $chasis,
            'motor' => $motor,
        ]);
    }

     public function postDispoTemplateNumSendNew(Request $request){
        if (!$request->hasFile('archivo')) {
            return response()->json([
                'error' => true,
                'message' => 'No se envió ningún archivo.'
            ], 400);
        }

        $file = $request->file('archivo');

        // Abrimos el archivo CSV
        $celularesArr = [];
       
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ','); // leemos cabeceras
            $celularIndex = array_search('Numero_Celular', $header);
            if ($celularIndex === false) {
                return response()->json([
                    'error' => true,
                    'message' => 'El archivo debe tener la cabecera: Numero_Celular.'
                ], 400);
            }

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Verificamos que la columna no esté vacía en la fila actual
                if (isset($data[$celularIndex]) && !empty(trim($data[$celularIndex]))) {
                    $celularesArr[] = trim($data[$celularIndex]);
                }
            }

            fclose($handle);
        }

        $celulares = implode(', ', $celularesArr);

        return response()->json([
            'error' => false,
            'celulares' => $celulares
           
        ]);
    }

    public function postDispositivosSendNew2(Request $request){
        Tenant::setCountryConnection(Session::get('Pais','EC'));
        $error=false; 
        $messageError='Datos recibidos correctamente y enviados a procesar';
        $tipoAlertaName='TEXTO';
        $mensajexmpp=null;
        $rutaCompleta=null;

        ##FORM1
        $tipoNoti    = (int) $request->input('tipoNoti');
        $titleNoti   = $request->input('titleNoti');
        $subTitleNoti= $request->input('subTitleNoti');

        $tipoMode        = (int) $request->input('tipoMode');
        $colorTitleNoti  = $request->input('colorTitleNoti', '#000000');
        $colorSubTitleNoti = $request->input('colorSubTitleNoti', '#000000');
        $colorFondoNoti  = $request->input('colorFondoNoti', '#ffffff');

        // -----------------------------
        // Botones (principal/secundario)
        // -----------------------------
        $buttons = $request->input('buttons', []);
        $btnPrincipal  = $buttons['principal']  ?? null;
        $btnSecundario = $buttons['secundario'] ?? null;

        $normalizeBtn = function (?array $btn, string $role) {
            if (empty($btn)) return null;

            $text      = trim((string)($btn['text'] ?? ''));
            $type      = (string)($btn['type'] ?? ''); // url|call|whatsapp
            $bg        = (string)($btn['bg'] ?? '#eeeeee');
            $textColor = (string)($btn['text_color'] ?? '#000000');

            if ($text === '' || $type === '') return null;

            if ($type === 'url') {
                $url = trim((string)($btn['url'] ?? ''));
                if ($url === '') return null;

                return [
                    'Role'      => $role,      // PRI | SEC
                    'Type'      => 'url',
                    'Text'      => $text,
                    'Url'       => $url,
                    'BgColor'        => $bg,
                    'TextColor' => $textColor,
                ];
            }

            if ($type === 'call' || $type === 'whatsapp') {
                $phone = trim((string)($btn['phone'] ?? ''));
                if ($phone === '') return null;

                return [
                    'Role'      => $role,
                    'Type'      => $type,      // call | whatsapp
                    'Text'      => $text,
                    'Phone'     => $phone,
                    'BgColor'   => $bg,
                    'TextColor' => $textColor,
                ];
            }

            return null;
        };

        $btns = array_values(array_filter([
            $normalizeBtn($btnPrincipal,  'PRI'),
            $normalizeBtn($btnSecundario, 'SEC'),
        ]));

        // Si NO es Multimedia, ignoramos botones
        if ($tipoNoti !== 1) {
            $btns = [];
        }

        // Para NotifyLog: 1 url, 1 ws, 1 contacto (prioridad: principal y primer match)
        $urlNoti = null; $ws = null; $contacto = null;
        foreach ($btns as $b) {
            if ($b['Type'] === 'url' && $urlNoti === null) $urlNoti = $b['Url'];
            if ($b['Type'] === 'whatsapp' && $ws === null) $ws = $b['Phone'];
            if ($b['Type'] === 'call' && $contacto === null) $contacto = $b['Phone'];
        }

        // -----------------------------
        // FORM 3
        // -----------------------------
        $programation_h = $request->input('programation_h');
        $schedule_date_h = $request->input('schedule_date_h');
        $schedule_time_h = $request->input('schedule_time_h');

        $schedule_daily_time_h = $request->input('schedule_daily_time_h');
        $schedule_daily_start_h = $request->input('schedule_daily_start_h');
        $schedule_daily_end_h  = $request->input('schedule_daily_end_h');

        $campaignName = $request->input('titleCampaing_h');
        $campaignDescription = $request->input('subTitleCampaing_h');
        $customRuleJson = $request->input('custom_rule_json_h');

        //PROXIMAMENTE
        
        $nivel = null;
        $contacto = null;
        $html= $request->input('htmlNoti');
        $rutaHtmlCompleta = null;
        
         // Guardar HTML si aplica
        if (!empty($html)) {
            $nombreArchivoHtml = 'html_' . time() . '.html';
            $rutaHtml = public_path('assets/upload/HTML/' . $nombreArchivoHtml);
        
            if (file_put_contents($rutaHtml, $html) !== false) {
                $rutaHtmlCompleta = 'https://notify.24hm.net/assets/upload/HTML/' . $nombreArchivoHtml;
            } else {
                $error = true;
                $messageError = 'No se pudo guardar el archivo HTML correctamente.';
            }
        }

        // Subida de imagen (customFileNoti)
        if ($request->hasFile('customFileNoti')) {
            $imagen = $request->file('customFileNoti');
            $extensionesPermitidas = ['png', 'jpg', 'jpeg'];
            $extension = $imagen->getClientOriginalExtension();
            
            if (!in_array($extension, $extensionesPermitidas)) {
                $error = true;
                $messageError = 'El archivo debe ser PNG o JPG.';
            }
            
            $tamanoMaximo = 2 * 1024 * 1024; // 2 MB
            
            if ($imagen->getSize() > $tamanoMaximo) {
                $error = true;
                $messageError = 'El archivo debe ser de máximo 2 MB.';
            }
            if (!$error) {
                $imagenNombre = time() . '_' . str_replace(' ', '_', $imagen->getClientOriginalName());
                $ruta = $imagen->move(public_path('assets/upload'), $imagenNombre);
                if ($ruta) {
                    $rutaCompleta = 'https://notify.24hm.net/assets/upload/' . $imagenNombre;
                } else {
                    $error = true;
                    $messageError = 'No se pudo procesar la imagen correctamente, por favor intente más tarde.';
                }
            }
        }
        $tipo=2; $subtipo=6;
        // Dispositivos
        $dispositivosInput = $request->input('dispositivos-input');
        $dispositivosData = json_decode($dispositivosInput, true);
        $arrayInsert[]=null;
        
        if(!$error){
            switch ($tipoNoti) {
                case 0:// Informativa
                    $tipoAlertaName='Informativa';
                    $tipo=7; $subtipo=2;
                    $mensajexmpp=json_encode(['Informativa' => [
                            'Titulo'    => $titleNoti,
                            'Subtitulo' => $subTitleNoti,
                            'Image'     => $rutaCompleta,
                        ]
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    break;
                case 1:
                    // Alerta de tipo Multimedia
                    $tipoAlertaName='Multimedia';
                    $tipo=2; $subtipo=6;
                    $mdl = ($colorTitleNoti === '#000000' && $colorSubTitleNoti === '#000000' && $colorFondoNoti === '#ffffff')? [ $tipoMode ]: [ $tipoMode, $colorTitleNoti, $colorSubTitleNoti, $colorFondoNoti ];
                    $payload = [
                        'Multimedia' => [
                            'Titulo'    => $titleNoti,
                            'Subtitulo' => $subTitleNoti,
                            'Image'     => $rutaCompleta,
                            'Mdl'       => $mdl,
                        ]
                    ];
                    // Botones (0, 1 o 2)
                    if (!empty($btns)) {
                        $payload['Multimedia']['Btns'] = $btns;
                    }

                    $mensajexmpp = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    
                
                    break;
                case 2:
                    $tipoAlertaName = 'HTML';
                    $tipo = 7; $subtipo = 2;
                    $mensajexmpp = json_encode([
                        'Html' => [
                            'Titulo'    => $titleNoti,
                            'Subtitulo' => $subTitleNoti,
                            'Image'     => $rutaCompleta,
                            'Url'       => $rutaHtmlCompleta,
                        ]
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    break;
                case 2:
                    // Alerta de tipo Vencimiento
                    $tipoAlertaName='Vencimiento';
                    //$mensajexmpp='{"Multimedia": {"Titulo": "'.$title.'","Subtitulo": "'.$subtitle.'","Nivel": '.$nivel.',"Call": "'.$contacto.'","Ws": "'.$ws.'"}}';
                    $tipo=2; $subtipo=4;
                    break;
                case 4:
                    // Alerta de tipo TEXTO
                    $tipoAlertaName='Texto';
                    $mensajexmpp=$subTitleNoti;
                    $tipo=1; $subtipo=6;
                    break;
                default:
                    $tipo=1; $subtipo=6;
                    $mensajexmpp=$subTitleNoti;
                    break;
            }
    
            $status = $programation_h==0?'sent':'scheduled';
            
            $sql = "
                DECLARE @IdCampaign BIGINT;
                DECLARE @IdSend BIGINT;

                EXEC dbo.spNotifyWizard_Save
                    @CampaignName        = ?,
                    @CampaignDescription = ?,
                    @Country             = ?,
                    @App                 = ?,
                    @UsuarioTransaccion  = ?,

                    @TipoNoti            = ?,
                    @TipoAlertaName      = ?,
                    @Tipo                = ?,
                    @SubTipo             = ?,
                    @TitleNoti           = ?,
                    @SubTitleNoti        = ?,
                    @ImagenUrl           = ?,
                    @HtmlUrl             = ?,
                    @MensajeXMPP         = ?,
                    @MensajeHTML         = ?,
                    @UrlLegacy           = ?,
                    @WhatsappLegacy      = ?,
                    @ContactoLegacy      = ?,
                    @Ip                  = ?,
                    @Status              = ?,
                    @Program             = ?,
                    @ScheduleDate        = ?,
                    @ScheduleTime        = ?,
                    @DailyTime           = ?,   
                    @DailyStartDate      = ?,  
                    @DailyEndDate        = ?,
                    @CustomRuleJson      = ?,
                    @JSONDetalle         = ?, 
                    @IdCampaign          = @IdCampaign OUTPUT,
                    @IdSend              = @IdSend OUTPUT;

                SELECT
                    @IdCampaign AS IdCampaign,
                    @IdSend     AS IdSend;
                ";
 
                $bindings = [
                    $campaignName,
                    $campaignDescription,
                    Session::get('Pais','EC'), // o null si no lo usas
                    Session::get('AppNotify'),
                    Session::get('UserHM'),

                    $tipoNoti,
                    $tipoAlertaName,
                    $tipo,
                    $subtipo,
                    $titleNoti,
                    $subTitleNoti,
                    $rutaCompleta,
                    $rutaHtmlCompleta,
                    $mensajexmpp,
                    $html,
                    $urlNoti,     
                    $ws,          
                    $contacto,   
                    getIPAddress(),
                    $status,
                    $programation_h,
                     // ONCE
                    ((int)$programation_h === 1 ? $schedule_date_h : null),
                    ((int)$programation_h === 1 ? $schedule_time_h : null),

                    // DAILY
                    ((int)$programation_h === 2 ? $schedule_daily_time_h : null),
                    ((int)$programation_h === 2 ? $schedule_daily_start_h : null),
                    ((int)$programation_h === 2 ? $schedule_daily_end_h : null),
                    $customRuleJson,
                    $dispositivosInput, 
                ];
                try {
                    $row = DB::selectOne($sql, $bindings);

                    return response()->json([
                        'error' => false,
                        'message' => 'Campaña y envío guardados correctamente.',
                        'IdCampaign' => $row->IdCampaign ?? null,
                        'IdSend' => $row->IdSend ?? null,
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Error al guardar el envío: '.$e->getMessage(),
                    ]);
                }
        }
        
        return  response()->json(['error' => $error,'message' => $messageError, 'arrayInsert'=>$arrayInsert]);
    }

    public function notificationWizard2($country, $app_name){
        $overview=array();
        $overview['CurrentDate']=Carbon::now()->format('Y-m-d');
        $overview['BeforeDate']=Carbon::now()->subDays(7)->format('Y-m-d');
        $overview=getCustomDataByApp(Session::get('AppNAME'));
        $overview['postDispositivos']=env('BASE_POST').'getDispositivosAlt';
        $overview['postDispositivosSendNew2']=env('BASE_POST').'postDispositivosSendNew2';
        $overview['postDispoTemplateSendNew']=env('BASE_POST').'postDispoTemplateSendNew';
        $overview['postDispoTemplateNumSendNew']=env('BASE_POST').'postDispoTemplateNumSendNew';
        $overview['listGroups'] = $this->getListGroupSub();
        $overview['listTipoEntidad'] = $this->getListTipoEnt();
        return view('notificationWizard2',['overview' => $overview]);
    }
}
