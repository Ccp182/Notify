@extends('layouts.generalContent')
@section('title','- Administrador Notificaciones')

@section('css')
<link href="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/css/dataTables.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/custom.css') }}" id="app-style" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<style>
    
    
</style>

<div class="container-fluid h-100">
     <div class="row h-100 ms-1">
          <div class="col-lg-5 col-md-5 col-sm-12">
               <div class="card">
                    <div class="card-body">

                         <h4 class="card-title">Crear Notificación</h4>
                         <form id="sendNotificationfrm" method="POST" class="form-horizontal" data-bitwarden-watching="1">
                              <div class="row mb-3">
                                   <label for="title-input" class="col-sm-2 col-form-label">Título</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="text" placeholder="" id="title-input" name="title-input" required maxlength="200">
                                        <div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Numero máximo de caracteres: 200</div>
                                   </div>
                              </div>
                              <!-- end row -->
                              <div class="row mb-3">
                                   <label for="subtitle-input" class="col-sm-2 col-form-label">Subtítulo</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="text" placeholder="" id="subtitle-input" name="subtitle-input" required maxlength="200">
                                        <div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Numero máximo de caracteres: 200</div>
                                   </div>
                              </div>
                              <!-- end row -->
                              <!--<div class="row mb-3">
                                   <label for="html-input" class="col-sm-2 col-form-label">Mensaje HTML</label>
                                   <div class="col-sm-10">
                                        <textarea id="mensaje-input" class="form-control" rows="2" name="mensaje-input" disabled></textarea>
                                   </div>
                              </div>-->
                              <!-- end row -->
                              <div class="row mb-3">
                                   <label for="url-input" class="col-sm-2 col-form-label">URL</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="url" placeholder="https://huntermonitoreo.com" id="url-input" name="url-input">
                                   </div>
                              </div>
                              <!-- end row -->
                              <!--<div class="row mb-3">
                                   <label for="date-input" class="col-sm-2 col-form-label">Fecha vencimiento</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="datetime-local" value="" id="date-input" name="date-input">
                                   </div>
                              </div>-->
                              <!-- end row -->
                              <!--<div class="row mb-3">
                                   <label for="title-input" class="col-sm-2 col-form-label">Número de Whatsapp</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="text" placeholder="" id="ws-input" name="ws-input" disabled>
                                   </div>
                              </div>-->
                              <!-- end row -->
                              <!--<div class="row mb-3">
                                   <label for="title-input" class="col-sm-2 col-form-label">Numero de Contacto</label>
                                   <div class="col-sm-10">
                                        <input class="form-control" type="text" placeholder="" id="contacto-input" name="contacto-input" disabled>
                                   </div>
                              </div>-->
                              <!-- end row -->
                              <div class="row mb-3">
                                   <label for="url-input" class="col-sm-2 col-form-label">Imagen</label>
                                   <div class="col-sm-10">
                                        <input type="file" class="form-control" id="customFileNotification" name="customFileNotification" required>
                                        <div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Seleccione una imagen PNG o JPG (preferiblemente 800x600) de máximo 2 MB de peso.</div>
                                   </div>
                              </div>
                               <!-- end row -->
                               <!--<div class="row mb-3">
                                   <label class="col-sm-2 col-form-label">Nivel Vencimiento</label>
                                   <div class="col-sm-10">
                                        <select class="form-select" aria-label="Default select example" name="selectNivelNotification" id="selectNivelNotification" disabled>
                                             <option value="1" selected="">Nivel 1</option>
                                             <option value="2">Nivel 2</option>
                                             <option value="3">Nivel 3</option>
                                        </select>
                                   </div>
                              </div>-->
                              <!-- end row -->
                              <div class="row mb-3">
                                   <label class="col-sm-2 col-form-label">Tipo Alerta</label>
                                   <div class="col-sm-10">
                                        <select class="form-select" aria-label="Default select example" name="selectTipoNotification" id="selectTipoNotification">
                                             <option value="0" selected="">Multimedia</option>
                                             <!--<option value="1">Vencimiento</option>-->
                                             <!--<option value="2">HTML</option>-->
                                             <option value="3">Texto</option>
                                        </select>
                                   </div>
                              </div>
                              <!-- end row -->
                              
                              <div class="modal-footer">
                                   <button type="reset" class="btn btn-secondary" data-dismiss="modal">Reset</button>
                                   <button type="submit" class="btn btn-primary">Enviar <i class="mdi mdi-send mdi-rotate-315 ms-1"></i></button>
                              </div>
                         </form>
                    </div>
               </div>
          </div> <!-- end col -->
          <div class="col-lg-7 col-md-7 col-sm-12">
               <div class="card">
                    <div class="card-body">
                    <h4 class="card-title">Lista de envío</h4>
                    <p class="card-title-desc">Seleccionar uno o varios dispositivos a enviar la alerta</p>    
                    
                    <div class="table-responsive">
                         <table class="table mb-0" id="datatable-dispositivos">

                              <thead class="table-light">
                                   <tr>
                                   <th>App</th>
                                        <th>Id</th>
                                       
                                        <th>Usuario</th>
                                        <th># Celular</th>
                                       
                                        <th>Plataforma</th>
                                        <th>Modelo</th>
                                   </tr>
                              </thead>
                              <tbody>
                             
                              </tbody>
                         </table>
                    </div>

                    </div>
               </div>
          </div>
     </div>

</div>
    
@endsection

@section('script')
<script>
     var postDispositivos="{{$overview['postDispositivos']}}";
     var postDispositivosSend="{{$overview['postDispositivosSend']}}";
</script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/Select-2.0.0/js/dataTables.select.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/Select-2.0.0/js/select.bootstrap4.min.js') }}"></script>
<script>
     $(document).ready(function(){

          dispositivos = setDatatableDispositivos();
    });
    
</script>

<script src="{{ asset('assets/js/pages/datatableDispositivos.init.js') }}"></script>


@endsection