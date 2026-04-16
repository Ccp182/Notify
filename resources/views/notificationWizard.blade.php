@extends('layouts.generalContent')
@section('title','- Administrador Notificaciones')

@section('css')
<link href="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/css/dataTables.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/spectrum-colorpicker2/spectrum.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/custom.css') }}" id="app-style" rel="stylesheet" type="text/css" />
  
<link href="{{ asset('assets/css/multiple-select.min.css') }}" rel="stylesheet" type="text/css" />
    
<link href="{{ URL::asset('assets/libs/twitter-bootstrap-wizard/prettify.css') }}" rel="stylesheet">



@endsection

@section('content')
<style>
     .android_template{
          background-image: url("{{ URL::asset('assets/images/android_template.png') }}");
          background-position: 0px 0px;
          background-size: cover;
     }
     .ios_template{
          background-image: url("{{ URL::asset('assets/images/ios_template.png') }}");
          background-position: 0px 0px;
          background-size: cover;
     }

     .android_template_design{
          background-image: url("{{ URL::asset('assets/images/android_template.png') }}");
          background-position: 0px 0px;
          background-size: cover;
     }
     body {
          background-color: #F1F5F7!important;
     }

     .twitter-bs-wizard .twitter-bs-wizard-pager-link li a {
          background-color: {{$overview['COLOR']}} !important;
     }
     .twitter-bs-wizard .twitter-bs-wizard-nav .step-number {
          border: 2px solid {{$overview['COLOR']}} !important;
          color: {{$overview['COLOR']}} !important;
     }
     .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active .step-number {
          background-color: {{$overview['COLOR']}} !important;
          color: #fff !important;
     }
</style>

<div class="container-fluid h-100 notification">
     <div class="row h-100 ms-1">

     <div class="col-lg-12">
          
                    <h4 class="card-title mb-4">Envío de Notificaciones</h4>

                    <div id="progrss-wizard" class="twitter-bs-wizard">
                         <div class="card">
                              <div class="card-body p-3">
                                   <div class="block_nav"></div>
                                   <ul class="twitter-bs-wizard-nav nav-justified">
                                        
                                        <li class="nav-item">
                                             <a href="#design" class="nav-link" data-toggle="tab">
                                                  <span class="step-number"><i class="mdi mdi-card-text-outline"></i></span>
                                                  <span class="step-title">Notificación</span>
                                             </a>
                                        </li>
                                        <li class="nav-item">
                                             <a href="#audience" class="nav-link" data-toggle="tab">
                                                  <span class="step-number"><i class="mdi mdi-devices"></i></span>
                                                  <span class="step-title">Público Objetivo</span>
                                             </a>
                                        </li>
                                        <li class="nav-item">
                                             <a href="#programation" class="nav-link" data-toggle="tab">
                                                  <span class="step-number"><i class="mdi mdi-timetable"></i></span>
                                                  <span class="step-title">Programación</span>
                                             </a>
                                        </li>
                                        <li class="nav-item">
                                             <a href="#revision" class="nav-link" data-toggle="tab">
                                                  <span class="step-number"><i class="mdi mdi-comment-edit"></i></span>
                                                  <span class="step-title">Revisión</span>
                                             </a>
                                        </li>
                                   </ul>

                                   <div id="bar" class="progress mt-4 mb-3">
                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"></div>
                                   </div>
                              </div>
                         </div>
                         <div class="card">
                              <div class="card-body">
                                   <div class="tab-content twitter-bs-wizard-tab-content pt-1">
                                        <div class="tab-pane" id="design">
                                             <div class="row">
                                                  <div class="col-lg-7">
                                                       <form id="designfrm"  method="POST" class="form-horizontal" data-bitwarden-watching="1">
                                                            <div class="row">
                                                                 <div class="col-lg-12 mb-4">
                                                                      <div class="row">
                                                                           <input  type="hidden" name="tipoNoti" id="tipoNoti" value="0"/>
                                                                           <div class="col-lg-4">
                                                                                <label class="form-label mt-2">Tipo de notificación</label>
                                                                           </div>
                                                                           <div class="col-lg-8">
                                                                                <ul class="nav nav-pills nav-justified tipoNotiClass" role="tablist">
                                                                                     <li class="nav-item waves-effect waves-light">
                                                                                          <a class="nav-link active" data-bs-toggle="tab" href="#SimpleNoti" role="tab" title="Notificación simple" aria-selected="false">
                                                                                               <span class="nav-Icon d-sm-none"><i class="mdi mdi-card-text-outline font-size-15"></i></span>
                                                                                               <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-card-text-outline font-size-15 me-1"></i>Informativa</span> 
                                                                                          </a>
                                                                                     </li>
                                                                                     <li class="nav-item waves-effect waves-light">
                                                                                          <a class="nav-link" data-bs-toggle="tab" href="#MultiNoti" role="tab" title="Notificación Multimedia" aria-selected="true">
                                                                                               <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-size-select-actual font-size-15"></i></span>
                                                                                               <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-size-select-actual font-size-15 me-1"></i>Multimedia</span> 
                                                                                          </a>
                                                                                     </li>
                                                                                     <li class="nav-item waves-effect waves-light">
                                                                                          <a class="nav-link" data-bs-toggle="tab" href="#HTMLNoti" role="tab" title="Notificación HTML" aria-selected="true">
                                                                                               <span class="nav-Icon d-sm-none"><i class="mdi mdi-table-eye font-size-15"></i></span>
                                                                                               <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-table-eye font-size-15 me-1"></i>HTML</span> 
                                                                                          </a>
                                                                                     </li>
                                                                                </ul>
                                                                           </div>
                                                                      </div>
                                                                      
                                                                 </div>
                                                                 <div class="col-lg-12">
                                                                      <div class="mb-3">
                                                                           <label class="form-label" for="titleNoti">Título de la notificación</label>
                                                                           {{--<div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Número máximo de caracteres: 200</div>--}}
                                                                           <input class="form-control" type="text" placeholder="" id="titleNoti" name="titleNoti" required maxlength="100">
                                                                           
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-lg-12">
                                                                      <div class="mb-3">
                                                                           <label class="form-label" for="subTitleNoti">Texto de la notificación</label>
                                                                           <textarea id="subTitleNoti" class="form-control"  name="subTitleNoti" required maxlength="200" rows="3" placeholder="Este textarea tiene un límite de 200 caracteres."></textarea>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-lg-12">
                                                                      <div class="mb-3">
                                                                           <label class="form-label" for="customFileNoti">Imagen</label> <span id="opImgNoti" class="text-muted font-size-10">(opcional)</span>
                                                                           <input type="file" class="form-control" id="customFileNoti" name="customFileNoti" >
                                                                           <div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Seleccione una imagen PNG o JPG (preferiblemente 800x600) de máximo 2 MB.</div>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-lg-12 hmtlSetNoti d-none">
                                                                      <div class="mb-3">
                                                                           <label class="form-label" for="htmlNoti">Codigo HTML</label>
                                                                           <textarea id="htmlNoti" class="form-control"  name="htmlNoti" placeholder="Ingrese su código HTML aquí..."></textarea>
                                                                      </div>
                                                                 </div>
                                                                 
                                                                 <div class="row mb-3 modelDesignNoti d-none">
                                                                      <h4 class="card-title mt-4" style="font-weight: 800;">Diseño del Modal</h4>
                                                                      <hr/>
                                                                      <div class="col-lg-12">
                                                                           <label class="form-label mt-4">Tipo de Modelo(Card)</label>
                                                                           <input  type="hidden" name="tipoMode" id="tipoMode" value="1"/>
                                                                           <ul class="nav nav-pills nav-justified tipoDesignNotiClass" role="tablist">
                                                                                {{--<li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link" data-bs-toggle="tab" href="#SideImageNoti" role="tab" title="Side Image Card" aria-selected="false">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-text font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-text font-size-18 me-1"></i><br>Side<br>Image</span> 
                                                                                     </a>
                                                                                </li>--}}
                                                                                <li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link active" data-bs-toggle="tab" href="#InfoCardNoti" role="tab" title="Info Card" aria-selected="true">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-format-align-center font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-format-align-center font-size-18 me-1"></i><br>Info<br>Vertical</span> 
                                                                                     </a>
                                                                                </li>
                                                                                <li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link" data-bs-toggle="tab" href="#HeaderMediaCardNoti" role="tab" title="Header Media Card" aria-selected="true">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-format-line-style font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-format-line-style font-size-18 me-1"></i><br>Header<br>Media</span> 
                                                                                     </a>
                                                                                </li>
                                                                                <li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link" data-bs-toggle="tab" href="#FullImageCardNoti" role="tab" title="Full Image Card" aria-selected="true">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-size-select-actual font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-size-select-actual font-size-18 me-1"></i><br>Full<br>Image</span> 
                                                                                     </a>
                                                                                </li>
                                                                                <li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link" data-bs-toggle="tab" href="#CaptionedImageCardNoti" role="tab" title="Captioned Image Card" aria-selected="true">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-area-close font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-area-close font-size-18 me-1"></i><br>Captioned<br>Image</span> 
                                                                                     </a>
                                                                                </li>
                                                                                <li class="nav-item waves-effect waves-light">
                                                                                     <a class="nav-link" data-bs-toggle="tab" href="#TopSideImageCardNoti" role="tab" title="Top Side Image Card" aria-selected="true">
                                                                                          <span class="nav-Icon d-sm-none"><i class="mdi mdi-dock-top font-size-18"></i></span>
                                                                                          <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-dock-top font-size-18 me-1"></i><br>Top Side<br>Image</span> 
                                                                                     </a>
                                                                                </li>
                                                                           </ul>
                                                                      </div>
                                                                      <div class="col-lg-4 mt-4">
                                                                           <div class="mb-2">
                                                                                <label class="form-label">Color Título</label>
                                                                                <input type="text" class="form-control" id="colorTitleNoti" name="colorTitleNoti" value="#000000">
                                                                           </div>
                                                                      </div>
                                                                      <div class="col-lg-4 mt-4">
                                                                           <div class="mb-2">
                                                                                <label class="form-label">Color Texto</label>
                                                                                <input type="text" class="form-control" id="colorSubTitleNoti" name="colorSubTitleNoti" value="#000000">
                                                                           </div>
                                                                      </div>
                                                                      <div class="col-lg-4 mt-4">
                                                                           <div class="mb-2">
                                                                                <label class="form-label">Fondo Modal</label>
                                                                                <input type="text" class="form-control" id="colorModalNoti" name="colorFondoNoti" value="#ffffff">
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                                 
                                                                 <div class="row mt-4 buttonNoti  d-none">
                                                                      <h4 class="card-title mt-4" style="font-weight: 800;">Configuración Botón</h4>
                                                                      <hr/>
                                                                      <div class="col-lg-12 mb-4">
                                                                           <table>
                                                                                <tr>
                                                                                     <td>
                                                                                     <label class="form-label" for="buttonTextNoti">Mostrar Botón:</label>
                                                                                
                                                                                     </td>
                                                                                     <td>
                                                                                          <input type="checkbox" id="activateBtnNoti" switch="bool" name="activateBtnNoti" >
                                                                                          <label for="activateBtnNoti" data-on-label="Sí" data-off-label="No"></label>
                                                                                     </td>
                                                                                </tr>
                                                                           </table>
                                                                      </div>
                                                                      <div class="col-lg-12">
                                                                           <div class="mb-3">
                                                                                <label class="form-label" for="buttonTextNoti">Texto del Botón</label>
                                                                                <input class="form-control" type="text" placeholder="Ver más" id="buttonTextNoti" name="buttonTextNoti" disabled maxlength="20" >
                                                                                
                                                                           </div>
                                                                      </div>
                                                                      <div class="col-lg-12">
                                                                           <div class="mb-3">
                                                                                <label class="form-label" for="urlNoti">Url Botón</label>
                                                                                <input class="form-control" type="url" placeholder="https://huntermonitoreo.com" id="urlNoti" name="urlNoti" disabled>
                                                                                
                                                                           </div>
                                                                      </div>
                                                                      <div class="col-lg-4">
                                                                           <div class="mb-2">
                                                                                <label class="form-label">Fondo Boton</label>
                                                                                <input type="text" class="form-control" id="colorButtonNoti" name="colorButtonNoti" value="#eeeeee">
                                                                           </div>
                                                                      </div>
                                                                      <div class="col-lg-4">
                                                                           <div class="mb-2">
                                                                                <label class="form-label">Texto Boton</label>
                                                                                <input type="text" class="form-control" id="colorButtonTextNoti" name="colorButtonTextNoti" value="#00000">
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                            
                                                            
                                                       </form>
                                                  </div>
                                                  <div class="col-lg-5">

                                                       <div id="accordion-visualization" class="custom-accordion">
                                                            <div class="card mb-1 shadow-none">
                                                                 <a href="#collapseOneNoti" class="text-reset" data-bs-toggle="collapse" aria-expanded="true" aria-controls="collapseOneNoti">
                                                                      <div class="card-header" id="headingOne">
                                                                           <h6 class="m-0">
                                                                                <i class="mdi mdi-card-text"></i> Vista Previa Notificación
                                                                                {{--<i class="mdi mdi-minus float-end accor-plus-icon"></i>--}}
                                                                           </h6>
                                                                      </div>
                                                                 </a>
                         
                                                                 <div id="collapseOneNoti" class="collapse show" aria-labelledby="headingOne" >
                                                                      <div class="card-body">
                                                                           <div class="android_template">
                                                                                <div class="android_noti">
                                                                                     <div class="row">
                                                                                          <div class="col">
                                                                                               <div class="title_noti text-left">
                                                                                                    Título de la notificación
                                                                                               </div>
                                                                                               <div class="subTitle_noti text-left">
                                                                                                    Descripción de la notificación
                                                                                               </div>
                                                                                          </div>
                                                                                          <div class="col-auto img_noti d-none">
                                                                                               <img src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                          </div>
                                                                                     </div>
                                                                                     
                                                                                          
                                                                                </div>                          
                                                                           </div>
                                                                           <div class="text-muted mb-4 mt-1 font-size-12" style="text-align:center;"><strong>ANDROID</strong></div>
                                                                           <div class="ios_template">
                                                                                <div class="ios_noti">
                                                                                     <div class="row">
                                                                                          <div class="col">
                                                                                               <div class="title_noti text-left">
                                                                                                    Título de la notificación
                                                                                               </div>
                                                                                               <div class="subTitle_noti text-left">
                                                                                                    Descripción de la notificación
                                                                                               </div>
                                                                                          </div>
                                                                                          <div class="col-auto img_noti d-none">
                                                                                               <img src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                          </div>
                                                                                     </div>
                                                                                </div>                          
                                                                           </div>
                                                                           <div class="text-muted mb-4 mt-1 font-size-12" style="text-align:center;"><strong>IOS</strong></div>
                                                                           <hr/>
                                                                           <p class="mb-0 font-size-11">En esta vista previa, se ofrece una idea general de cómo se mostrará tu mensaje en un dispositivo móvil. La apariencia real del mensaje varía en función del dispositivo. Para obtener resultados precisos, prueba con un dispositivo real.</p>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="card mb-1 shadow-none d-none TwoNoti">
                                                                 <a href="#collapseTwoNoti" class="text-reset collapsed" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseTwoNoti">
                                                                      <div class="card-header" id="headingTwo">
                                                                           <h6 class="m-0">
                                                                                <i class="mdi mdi-tooltip-image-outline"></i>
                                                                                Vista Previa Diseño
                                                                                {{--<i class="mdi accor-plus-icon float-end mdi-minus"></i>--}}
                                                                           </h6>
                                                                      </div>
                                                                 </a>
                                                                 <div id="collapseTwoNoti" class="collapse" aria-labelledby="headingTwo">
                                                                      <div class="card-body">
                                                                           <div class="android_template_design">
                                                                                <div class="modal_design">
                                                                                     <div class="modal1">
                                                                                          <img class="close_noti" src="{{ URL::asset('assets/images/close.png') }}"/>
                                                                                          <img class="img_noti w-100" src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                          <div class="title_noti text-center mt-1">
                                                                                               Título de la notificación
                                                                                          </div>
                                                                                          <div class="subTitle_noti text-center mt-1">
                                                                                               Descripción de la notificación
                                                                                          </div>
                                                                                          <div class="button_noti text-right mt-3 d-none">
                                                                                               <button type="button" class="btn btn-secondary">Ver Más.</button>
                                                                                          </div>
                                                                                     </div>
                                                                                     <div class="modal2 d-none">
                                                                                          <img class="close_noti" src="{{ URL::asset('assets/images/close.png') }}"/>
                                                                                          <div class="title_noti text-left mb-1">
                                                                                               Título de la notificación
                                                                                          </div>
                                                                                          <img class="img_noti w-100" src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                          <div class="subTitle_noti text-left mt-2">
                                                                                               Descripción de la notificación
                                                                                          </div>
                                                                                          <div class="button_noti text-right mt-3 d-none">
                                                                                               <button type="button" class="btn btn-secondary">Ver Más.</button>
                                                                                          </div>
                                                                                     </div>
                                                                                     <div class="modal3 d-none">
                                                                                          <img class="close_noti" src="{{ URL::asset('assets/images/close.png') }}"/>
                                                                                          <img class="img_noti w-100" src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                     </div>

                                                                                     <div class="modal4 d-none">
                                                                                          <img class="close_noti" src="{{ URL::asset('assets/images/close.png') }}"/>
                                                                                          <img class="img_noti w-100" src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                          <div class="title_noti text-center mt-1">
                                                                                               Título de la notificación
                                                                                          </div>
                                                                                          <div class="button_noti text-right mt-3 d-none">
                                                                                               <button type="button" class="btn btn-secondary">Ver Más.</button>
                                                                                          </div>
                                                                                          
                                                                                     </div>
                                                                                     <div class="modal5 d-none">
                                                                                          <img class="close_noti" src="{{ URL::asset('assets/images/close.png') }}"/>
                                                                                          <table>
                                                                                               <tr>
                                                                                                    <td style="vertical-align: middle; padding-right:10px;">
                                                                                                         <img class="img_noti" src="{{ URL::asset('assets/images/Image-not-found.png') }}"/>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                         <div class="title_noti text-left">
                                                                                                              Título de la notificación
                                                                                                         </div>
                                                                                                         <div class="subTitle_noti text-left mt-1">
                                                                                                              Descripción de la notificación
                                                                                                         </div>
                                                                                                    </td>
                                                                                               </tr>
                                                                                          </table>
                                                                                          <div class="button_noti text-right mt-3 d-none">
                                                                                               <button type="button" class="btn btn-secondary">Ver Más.</button>
                                                                                          </div>
                                                                                     </div>
                                                                                </div>                     
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="tab-pane" id="audience">
                                             <div class="col-lg-12">
                                                  <div class="row">
                                                       <div class="col-md-2">
                                                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                                                 <a class="nav-link mb-2 active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Filtro Normal</a>
                                                                 <a class="nav-link mb-2" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Plantilla Chasis/Motor</a>
                                                                 <a class="nav-link mb-2" id="v-pills-number-tab" data-bs-toggle="pill" href="#v-pills-number" role="tab" aria-controls="v-pills-number" aria-selected="false">Plantilla</a>
                                                            </div>
                                                       </div>
                                                       <div class="col-md-10 ps-2">
                                                            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                                                                 <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                                                      <form id="filterDispositivo" method="POST">
                                                                           <div class="row">
                                                                                     
                                                                                
                                                                                <div class="col-sm-12 col-md-8 col-lg-4 col-xl-2" id="">
                                                                                     <label class="col-form-label font-weight-bold">Tipo Usuario</label>
                                                                                     <select id="tipoUser" class="form-select">
                                                                                          <option value="3" selected>TODOS</option>
                                                                                          <option value="1" >Usuario</option>
                                                                                          <option value="2" >SubUsuario</option>
                                                                                     </select>
                                                                                     
                                                                                </div>
                                                                                <div class="col-sm-12 col-md-8 col-lg-4 col-xl-2" id="">
                                                                                     <label class="col-form-label font-weight-bold">Plataforma</label>
                                                                                     <select id="plataforma" class="form-select">
                                                                                          <option value="TODOS" selected>TODOS</option>
                                                                                          <option value="ANDROID" >ANDROID</option>
                                                                                          <option value="IOS" >IOS</option>
                                                                                     </select>
                                                                                     
                                                                                </div>
                                                                                <div class="col-sm-12 col-md-8 col-lg-4 col-xl-3">
                                                                                     <label class="col-form-label font-weight-bold">Grupos SubUsuarios</label>
                                                                                     <select id="grupos" class="form-control select_grupos"  multiple="multiple" >
                                                                                          @if (isset($overview['listGroups']))
                                                                                               @foreach ($overview['listGroups'] as $grupo)
                                                                                               <option value="{{ $grupo['IdGroup'] }}" selected> - {{ $grupo['Group'] }}</option>
                                                                                               @endforeach
                                                                                          @endisset     
                                                                                     </select>
                                                                                     
                                                                                </div>
                                                                                <div class="col-sm-12 col-md-8 col-lg-4 col-xl-3">
                                                                                     <label class="col-form-label font-weight-bold">Tipo Entidad</label>
                                                                                     <select id="idTipoEnt" class="form-control select_tipo_entidad"  multiple="multiple" >
                                                                                          @if (isset($overview['listTipoEntidad']))
                                                                                               @foreach ($overview['listTipoEntidad'] as $tipo)
                                                                                               <option value="{{ $tipo['TEnt'] }}" selected> - {{ $tipo['Tipo'] }}</option>
                                                                                               @endforeach
                                                                                          @endisset     
                                                                                     </select>
                                                                                     
                                                                                </div>

                                                                                
                                                                                <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                       
                                                                                     <button type="submit" class="btn btn-brand-02 w-md"><i class="mdi mdi-magnify"></i> Buscar</button>
                                                                                </div>
                                                                           </div>
                                                                      </form>
                                                                 </div>
                                                                 <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                                                      <form id="filterTemplateDispositivo" method="POST" enctype="multipart/form-data">
                                                                           <div class="row">
                                                                                <input type="hidden" id="inputChasisList" name="chasisList" />
                                                                                <input type="hidden" id="inputMotorList" name="motorList" />
                                                                                
                                                                                <div class="col" id="">
                                                                                     <label class="col-form-label font-weight-bold">Archivo Chasis/Motor</label>
                                                                                     <input id="archivo" type="file" class="form-control"  name="archivo" accept=".csv" class="form-control-file" />                                                                      
                                                                                </div>
                                                                                

                                                                                
                                                                                <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                                     <button type="submit" class="btn btn-brand-02 w-md"><i class="mdi mdi-magnify"></i> Enviar</button>
                                                                                </div>
                                                                                <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                                     <a href="/assets/templates/plantilla_chasis_motor.csv" class="btn btn-success w-md" download>
                                                                                          <i class="mdi mdi-download"></i> Descargar plantilla Chasis Motor
                                                                                     </a>
                                                                                </div>
                                                                           </div>
                                                                      </form>
                                                                 </div>
                                                                  <div class="tab-pane fade" id="v-pills-number" role="tabpanel" aria-labelledby="v-pills-number-tab">
                                                                      <form id="filterTemplateNumDispositivo" method="POST" enctype="multipart/form-data">
                                                                           <div class="row">
                                                                                <input type="hidden" id="inputNumList" name="numList" />
                                                                                
                                                                                <div class="col" id="">
                                                                                     <label class="col-form-label font-weight-bold">Archivo</label>
                                                                                     <input id="archivoNum" type="file" class="form-control"  name="archivoNum" accept=".csv" class="form-control-file" />                                                                      
                                                                                </div>
                                                                                

                                                                                
                                                                                <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                                     <button type="submit" class="btn btn-brand-02 w-md"><i class="mdi mdi-magnify"></i> Enviar</button>
                                                                                </div>
                                                                                <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                                     <a href="/assets/templates/plantilla_numeros.csv" class="btn btn-success w-md" download>
                                                                                          <i class="mdi mdi-download"></i> Descargar plantilla
                                                                                     </a>
                                                                                </div>
                                                                           </div>
                                                                      </form>
                                                                 </div>
                                                                 
                                                            </div>
                                                       </div>
                                                  </div>
                                                       
                                                  <hr/>
                                                  <div class="table-responsive">
                                                       <table id="datatable-dispositivos-alt" class="table table-centered datatable dt-responsive nowrap" data-bs-page-length="5" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                            <thead class="table-light">
                                                                 <tr>
                                                                      <th style="width: 20px;">
                                                                           <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input" id="ordercheck">
                                                                                <label class="form-check-label mb-0" for="ordercheck">&nbsp;</label>
                                                                           </div>
                                                                      </th>
                                                                      <th>App</th>
                                                                      <th>Id</th>
                                                                      <th class="d-none">Name</th>
                                                                      <th>Nombre Usuario/SubUsuario</th>
                                                                      <th>Grupo</th>
                                                                      <th># Celular</th>
                                                                      <th>Plataforma</th>
                                                                      <th>Modelo</th>
                                                                      <th>Tipo Entidad</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                       </table>
                                                  </div>
                                                       
                                             </div>
                                        </div>
                                        <div class="tab-pane" id="programation">
                                             <div>
                                             {{--<form id="programationfrm">--}}
                                                  <div class="row">
                                                       <div class="col-12">
                                                            <div class="mb-3">
                                                                 <label class="form-label" for="program">Tipo de Programación de envío</label>
                                                                 <select class="form-select" id="program" name="program" require>
                                                                      <optgroup label="Notificación Única">
                                                                           <option value="0" selected>Ahora</option>
                                                                           <option value="1" disabled>Programado (Proximamente)</option>
                                                                      </optgroup>
                                                                      <optgroup label="Notificaciones Recurrentes">
                                                                           <option value="2" disabled>Diariamente (Proximamente)</option>
                                                                           <option value="3" disabled>Personalizar (Proximamente)</option>
                                                                      </optgroup>
                                                                 </select>

                                                            </div>
                                                       </div>

                                                       
                                                  </div>
                                                  
                                             {{--</form>--}}
                                             </div>
                                        </div>
                                        <div class="tab-pane" id="revision">
                                             <div class="row justify-content-center">
                                                  <div class="col-lg-8">
                                                       <div class="text-center">
                                                            {{--<div class="mb-4">
                                                                 <i class="mdi mdi-check-circle-outline text-success display-4"></i>
                                                            </div>--}}
                                                            <div>
                                                                 <h5 class="mb-5">Revisión Mensaje</h5>
                                                                 {{--<p class="text-muted">If several languages coalesce, the grammar of the resulting</p>--}}
                                                            </div>
                                                            <div id="rev_content mt-2">
                                                                 <div class="row">
                                                                      <div class="col-6 pe-4">
                                                                           <div class="rev_content_visual">
                                                                                <div id="accordion-visualization2" class="custom-accordion">
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                                      
                                                                      <div class="col-6 ps-4">
                                                                           <form id="NotificationSendfrm" method="POST">
                                                                                <input type="hidden" name="programation_h" id="programation_h">
                                                                                
                                                                           </form>
                                                                        

                                                                           <ul class="list-unstyled activity-wid">
                                                                                <li class="activity-list">
                                                                                     <div class="activity-icon avatar-xs">
                                                                                          <span class="avatar-title bg-primary-subtle  text-primary rounded-circle">
                                                                                               <i class="mdi mdi-card-text-outline"></i>
                                                                                          </span>
                                                                                     </div>
                                                                                     <div class="text-left">
                                                                                          <div><h5 class="font-size-13 mb-1">Contenido Aplicación</h5></div>
                                                                                          
                                                                                          <div><p class="text-muted mb-0 subtitle_input_text"></p></div>
                                                                                     </div>
                                                                                </li>
                                                                                <li class="activity-list">
                                                                                     <div class="activity-icon avatar-xs">
                                                                                          <span class="avatar-title bg-primary-subtle  text-primary rounded-circle">
                                                                                               <i class="mdi mdi-devices"></i>
                                                                                          </span>
                                                                                     </div>
                                                                                     <div class="text-left">
                                                                                          <div>
                                                                                               <h5 class="font-size-13 mb-1">Audiencia Objetivo</h5>
                                                                                          </div>
                                                                                          
                                                                                          <div>
                                                                                               <p class="text-muted mb-0 count_datatable"></p>
                                                                                          </div>
                                                                                     </div>
                                                                                </li>
                                                                                <li class="activity-list">
                                                                                     <div class="activity-icon avatar-xs">
                                                                                          <span class="avatar-title bg-primary-subtle  text-primary rounded-circle">
                                                                                               <i class="mdi mdi-timetable"></i>
                                                                                          </span>
                                                                                     </div>
                                                                                     <div class="text-left">
                                                                                          <div>
                                                                                               <h5 class="font-size-13 mb-1">Programación</h5>
                                                                                          </div>
                                                                                          
                                                                                          <div>
                                                                                               <p class="text-muted mb-0 programacion_text"></p>
                                                                                          </div>
                                                                                     </div>
                                                                                </li>
                                                                                
                                                                           </ul>
                                                                           
                                                                      </div>
                                                                      
                                                                 </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                                   
                              </div>
                         </div>
                         <div class="card">
                              <div class="card-body">
                                   <ul class="pager wizard twitter-bs-wizard-pager-link pt-0">
                                        <li class="previous d-none"><a href="javascript: void(0);">Ant</a></li>
                                        <li class="next d-none"><a href="javascript: void(0);">Sig</a></li>
                                        <li class="previous_custom disabled" ><a href="javascript: void(0);">Anterior</a></li>
                                        <button id="btnPublic" type="button" class="btn btn-success waves-effect waves-light float-end d-none">Publicar</button>
                                        <li class="next_custom float-end"><a href="javascript: void(0);">Siguiente</a></li>
                                        
                                        
                                   </ul>
                              </div>
                         </div>
                    </div>
          </div>
     </div>
          
</div>


    
@endsection

@section('script')
<script>
     var tableData;
     var postDispositivos="{{$overview['postDispositivos']}}";
     var postDispositivosSendNew="{{$overview['postDispositivosSendNew']}}";
     var postDispoTemplateSendNew="{{$overview['postDispoTemplateSendNew']}}";
</script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/Select-2.0.0/js/dataTables.select.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/datatables.net-libs/Select-2.0.0/js/select.bootstrap4.min.js') }}"></script>


<script src="{{ URL::asset('assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/twitter-bootstrap-wizard/prettify.js') }}"></script>
<script src="{{ URL::asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/spectrum-colorpicker2/spectrum.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/pages/form-wizard.init.js') }}"></script>
<script src="{{ URL::asset('assets/js/pages/form-advanced.init.js') }}"></script>
<script src="{{ URL::asset('assets/js/notification.init.js') }}"></script>
<script src="{{ URL::asset('assets/js/notificationTable.init.js') }}"></script>

<script src="{{ asset('assets/js/pages/multiple-select.min.js') }}"></script>
    
<script src="{{ asset('assets/js/pages/datatableDispositivos.init.js') }}"></script>


@endsection