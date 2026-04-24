@extends('layouts.app')

@section('title', '- Nueva Campaña')

@php
    $primaryColor = config('app.primary_color') ?: session('AppNotify.color', '#556ee6');
@endphp

@push('css')
<link href="{{ asset('assets/libs/datatables.net-libs/DataTables-2.0.0/css/dataTables.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/spectrum-colorpicker2/spectrum.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/multiple-select.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/twitter-bootstrap-wizard/prettify.css') }}" rel="stylesheet">
@endpush

@section('content')
<style>
    .android_template {
        background-image: url("{{ asset('assets/images/android_template.png') }}");
        background-position: 0 0;
        background-size: cover;
    }
    .ios_template {
        background-image: url("{{ asset('assets/images/ios_template.png') }}");
        background-position: 0 0;
        background-size: cover;
    }
    .android_template_design {
        background-image: url("{{ asset('assets/images/android_template.png') }}");
        background-position: 0 0;
        background-size: cover;
    }
    body { background-color: #F1F5F7 !important; }

    .twitter-bs-wizard .twitter-bs-wizard-pager-link li a { background-color: {{ $primaryColor }} !important; }
    .twitter-bs-wizard .twitter-bs-wizard-nav .step-number {
        border: 2px solid {{ $primaryColor }} !important;
        color: {{ $primaryColor }} !important;
    }
    .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active .step-number {
        
        color: #fff !important;
    }
</style>

<div class="container-fluid h-100 notification">
    <div class="row h-100 ms-1">

        <div class="col-lg-12">
            <h4 class="card-title mb-4">Nueva Campaña</h4>

            <div id="progrss-wizard" class="twitter-bs-wizard">

                <div class="card">
                    <div class="card-body p-3">
                        <div class="block_nav"></div>
                        <ul class="twitter-bs-wizard-nav nav-justified">
                            <li class="nav-item active">
                                <a href="#campaign" class="nav-link active" data-bs-toggle="tab">
                                    <span class="step-number"><i class="mdi mdi-bullhorn-outline"></i></span>
                                    <span class="step-title">Campaña</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#design" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number"><i class="mdi mdi-card-text-outline"></i></span>
                                    <span class="step-title">Notificación</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#audience" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number"><i class="mdi mdi-devices"></i></span>
                                    <span class="step-title">Público Objetivo</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#programation" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number"><i class="mdi mdi-timetable"></i></span>
                                    <span class="step-title">Programación</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#revision" class="nav-link" data-bs-toggle="tab">
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

                            {{-- STEP 1: CAMPAIGN (nombre + descripcion) --}}
                            <div class="tab-pane active show" id="campaign">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <form id="campaignfrm" method="POST" class="form-horizontal">
                                            @csrf
                                            <div class="row">
                                                <div class="col-12">
                                                    <h4 class="card-title mt-2 mb-0" style="font-weight: 800;">Identificación de la campaña</h4>
                                                </div>
                                                <div class="col-12"><hr class="mt-2 mb-3"></div>
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="titleCampaing">Nombre de la campaña <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="text" id="titleCampaing" name="titleCampaing" required maxlength="100" placeholder="Ej: Promoción abril 2026">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="subTitleCampaing">Descripción de la campaña <span class="text-danger">*</span></label>
                                                        <textarea id="subTitleCampaing" class="form-control" name="subTitleCampaing" required maxlength="200" rows="3" placeholder="Describe el objetivo y alcance de esta campaña (máx. 200 caracteres)."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-information-outline"></i>
                                                        Este nombre y descripción aparecerán en el listado de campañas.
                                                        Los siguientes pasos te guiarán para configurar el contenido de la notificación, el público objetivo y la programación de envío.
                                                    </small>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 2: DESIGN --}}
                            <div class="tab-pane" id="design">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <form id="designfrm" method="POST" class="form-horizontal" data-bitwarden-watching="1">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-12 mb-4">
                                                    <div class="row">
                                                        <input type="hidden" name="tipoNoti" id="tipoNoti" value="0"/>
                                                        <div class="col-lg-4">
                                                            <label class="form-label mt-2">Tipo de notificación</label>
                                                        </div>
                                                        <div class="col-lg-8">
                                                            <ul class="nav nav-pills nav-justified tipoNotiClass" role="tablist">
                                                                <li class="nav-item waves-effect waves-light">
                                                                    <a class="nav-link active" data-bs-toggle="tab" href="#SimpleNoti" role="tab" title="Notificación simple">
                                                                        <span class="nav-Icon d-sm-none"><i class="mdi mdi-card-text-outline font-size-15"></i></span>
                                                                        <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-card-text-outline font-size-15 me-1"></i>Informativa</span>
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item waves-effect waves-light">
                                                                    <a class="nav-link" data-bs-toggle="tab" href="#MultiNoti" role="tab" title="Notificación Multimedia">
                                                                        <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-size-select-actual font-size-15"></i></span>
                                                                        <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-size-select-actual font-size-15 me-1"></i>Multimedia</span>
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item waves-effect waves-light">
                                                                    <a class="nav-link" data-bs-toggle="tab" href="#HTMLNoti" role="tab" title="Notificación HTML">
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
                                                        <input class="form-control" type="text" id="titleNoti" name="titleNoti" required maxlength="100">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="subTitleNoti">Texto de la notificación</label>
                                                        <textarea id="subTitleNoti" class="form-control" name="subTitleNoti" required maxlength="200" rows="3" placeholder="Este textarea tiene un límite de 200 caracteres."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="customFileNoti">Imagen</label>
                                                        <span id="opImgNoti" class="text-muted font-size-10">(opcional)</span>
                                                        <input type="file" class="form-control" id="customFileNoti" name="customFileNoti">
                                                        <div class="text-muted mb-0 mt-1 font-size-10" style="text-align:right;">Seleccione una imagen PNG o JPG (preferiblemente 800x600) de máximo 2 MB.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 hmtlSetNoti d-none">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="htmlNoti">Código HTML</label>
                                                        <textarea id="htmlNoti" class="form-control" name="htmlNoti" placeholder="Ingrese su código HTML aquí..."></textarea>
                                                    </div>
                                                </div>

                                                <div class="row mb-3 mx-2 modelDesignNoti d-none">
                                                    <h4 class="card-title mt-4" style="font-weight: 800;">Diseño del Modal</h4>
                                                    <div class="col-12"><hr class="mt-1 mb-2"></div>
                                                    <div class="col-lg-12">
                                                        <div class="row noti-btn-section border rounded p-3 m-2 mb-3">
                                                            <div class="col-lg-12">
                                                                <label class="form-label mt-2">Tipo de Modelo (Card)</label>
                                                                <input type="hidden" name="tipoMode" id="tipoMode" value="1"/>
                                                                <ul class="nav nav-pills nav-justified tipoDesignNotiClass" role="tablist">
                                                                    <li class="nav-item waves-effect waves-light">
                                                                        <a class="nav-link active" data-bs-toggle="tab" href="#InfoCardNoti" role="tab">
                                                                            <span class="nav-Icon d-sm-none"><i class="mdi mdi-format-align-center font-size-18"></i></span>
                                                                            <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-format-align-center font-size-18 me-1"></i><br>Info<br>Vertical</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item waves-effect waves-light">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#HeaderMediaCardNoti" role="tab">
                                                                            <span class="nav-Icon d-sm-none"><i class="mdi mdi-format-line-style font-size-18"></i></span>
                                                                            <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-format-line-style font-size-18 me-1"></i><br>Header<br>Media</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item waves-effect waves-light">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#FullImageCardNoti" role="tab">
                                                                            <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-size-select-actual font-size-18"></i></span>
                                                                            <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-size-select-actual font-size-18 me-1"></i><br>Full<br>Image</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item waves-effect waves-light">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#CaptionedImageCardNoti" role="tab">
                                                                            <span class="nav-Icon d-sm-none"><i class="mdi mdi-image-area-close font-size-18"></i></span>
                                                                            <span class="nav-TextIcon d-sm-block"><i class="mdi mdi-image-area-close font-size-18 me-1"></i><br>Captioned<br>Image</span>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item waves-effect waves-light">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#TopSideImageCardNoti" role="tab">
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
                                                    </div>
                                                </div>

                                                <div class="row mt-2 mx-2 buttonNoti d-none align-items-center">
                                                    <div class="col">
                                                        <h4 class="card-title mt-2 mb-0" style="font-weight: 800;">Configuración Botón</h4>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button" id="btnAddNotiSection" class="btn btn-success btn-sm waves-effect waves-light">
                                                            <i class="mdi mdi-card-plus align-middle me-2"></i> Agregar
                                                        </button>
                                                    </div>
                                                    <div class="col-12"><hr class="mt-2 mb-2"></div>
                                                    <div class="col-12 spacebtn"></div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-lg-5">
                                        <div id="accordion-visualization" class="custom-accordion">
                                            <div class="card mb-1 shadow-none">
                                                <a href="#collapseOneNoti" class="text-reset" data-bs-toggle="collapse" aria-expanded="true" aria-controls="collapseOneNoti">
                                                    <div class="card-header" id="headingOne">
                                                        <h6 class="m-0"><i class="mdi mdi-card-text"></i> Vista Previa Notificación</h6>
                                                    </div>
                                                </a>
                                                <div id="collapseOneNoti" class="collapse show" aria-labelledby="headingOne">
                                                    <div class="card-body">
                                                        <div class="android_template">
                                                            <div class="android_noti">
                                                                <div class="row">
                                                                    <div class="col">
                                                                        <div class="title_noti text-left">Título de la notificación</div>
                                                                        <div class="subTitle_noti text-left">Descripción de la notificación</div>
                                                                    </div>
                                                                    <div class="col-auto img_noti d-none">
                                                                        <img src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-muted mb-4 mt-1 font-size-12" style="text-align:center;"><strong>ANDROID</strong></div>
                                                        <div class="ios_template">
                                                            <div class="ios_noti">
                                                                <div class="row">
                                                                    <div class="col">
                                                                        <div class="title_noti text-left">Título de la notificación</div>
                                                                        <div class="subTitle_noti text-left">Descripción de la notificación</div>
                                                                    </div>
                                                                    <div class="col-auto img_noti d-none">
                                                                        <img src="{{ asset('assets/images/Image-not-found.png') }}"/>
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
                                                        <h6 class="m-0"><i class="mdi mdi-tooltip-image-outline"></i> Vista Previa Diseño</h6>
                                                    </div>
                                                </a>
                                                <div id="collapseTwoNoti" class="collapse" aria-labelledby="headingTwo">
                                                    <div class="card-body">
                                                        <div class="android_template_design">
                                                            <div class="modal_design">
                                                                <div class="modal1">
                                                                    <img class="close_noti" src="{{ asset('assets/images/close.png') }}"/>
                                                                    <img class="img_noti w-100" src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                    <div class="title_noti text-center mt-1">Título de la notificación</div>
                                                                    <div class="subTitle_noti text-center mt-1">Descripción de la notificación</div>
                                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                                        <div class="button_noti button_noti_pri d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                        <div class="button_noti button_noti_sec d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal2 d-none">
                                                                    <img class="close_noti" src="{{ asset('assets/images/close.png') }}"/>
                                                                    <div class="title_noti text-left mb-1">Título de la notificación</div>
                                                                    <img class="img_noti w-100" src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                    <div class="subTitle_noti text-left mt-2">Descripción de la notificación</div>
                                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                                        <div class="button_noti button_noti_pri d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                        <div class="button_noti button_noti_sec d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal3 d-none">
                                                                    <img class="close_noti" src="{{ asset('assets/images/close.png') }}"/>
                                                                    <img class="img_noti w-100" src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                </div>
                                                                <div class="modal4 d-none">
                                                                    <img class="close_noti" src="{{ asset('assets/images/close.png') }}"/>
                                                                    <img class="img_noti w-100" src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                    <div class="title_noti text-center mt-1">Título de la notificación</div>
                                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                                        <div class="button_noti button_noti_pri d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                        <div class="button_noti button_noti_sec d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal5 d-none">
                                                                    <img class="close_noti" src="{{ asset('assets/images/close.png') }}"/>
                                                                    <table>
                                                                        <tr>
                                                                            <td style="vertical-align: middle; padding-right:10px;">
                                                                                <img class="img_noti" src="{{ asset('assets/images/Image-not-found.png') }}"/>
                                                                            </td>
                                                                            <td>
                                                                                <div class="title_noti text-left">Título de la notificación</div>
                                                                                <div class="subTitle_noti text-left mt-1">Descripción de la notificación</div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                                        <div class="button_noti button_noti_pri d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
                                                                        <div class="button_noti button_noti_sec d-none"><button type="button" class="btn btn-secondary">Ver más</button></div>
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

                            {{-- STEP 2: AUDIENCE --}}
                            <div class="tab-pane" id="audience">
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                                <a class="nav-link mb-2 active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab">Filtro Normal</a>
                                                <a class="nav-link mb-2" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab">Plantilla Chasis/Motor</a>
                                                <a class="nav-link mb-2" id="v-pills-number-tab" data-bs-toggle="pill" href="#v-pills-number" role="tab">Plantilla</a>
                                            </div>
                                        </div>
                                        <div class="col-md-10 ps-2">
                                            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                                                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel">
                                                    <form id="filterDispositivo" method="POST">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-12 col-md-8 col-lg-4 col-xl-2">
                                                                <label class="col-form-label fw-bold">Tipo Usuario</label>
                                                                <select id="tipoUser" class="form-select">
                                                                    <option value="3" selected>TODOS</option>
                                                                    <option value="1">Usuario</option>
                                                                    <option value="2">SubUsuario</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-12 col-md-8 col-lg-4 col-xl-2">
                                                                <label class="col-form-label fw-bold">Plataforma</label>
                                                                <select id="plataforma" class="form-select">
                                                                    <option value="TODOS" selected>TODOS</option>
                                                                    <option value="ANDROID">ANDROID</option>
                                                                    <option value="IOS">IOS</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-12 col-md-8 col-lg-4 col-xl-3">
                                                                <label class="col-form-label fw-bold">Grupos de SubUsuarios</label>
                                                                <select id="grupos" class="form-control select_grupos" multiple="multiple">
                                                                    @foreach (($listGroups ?? []) as $grupo)
                                                                        <option value="{{ $grupo['GroupId'] ?? ($grupo['IdGroup'] ?? '') }}" selected> - {{ $grupo['GroupName'] ?? ($grupo['Group'] ?? '') }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-12 col-md-8 col-lg-4 col-xl-3">
                                                                <label class="col-form-label fw-bold">Tipo Entidad</label>
                                                                <select id="idTipoEnt" class="form-control select_tipo_entidad" multiple="multiple">
                                                                    @foreach (($listTipoEntidad ?? []) as $tipo)
                                                                        <option value="{{ $tipo['EntType'] ?? ($tipo['TEnt'] ?? '') }}" selected> - {{ $tipo['EntName'] ?? ($tipo['Tipo'] ?? '') }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                <button type="submit" class="btn btn-primary w-md"><i class="mdi mdi-magnify"></i> Buscar</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <div class="tab-pane fade" id="v-pills-profile" role="tabpanel">
                                                    <form id="filterTemplateDispositivo" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="row">
                                                            <input type="hidden" id="inputChasisList" name="chasisList"/>
                                                            <input type="hidden" id="inputMotorList"  name="motorList"/>
                                                            <div class="col">
                                                                <label class="col-form-label fw-bold">Archivo Chasis/Motor</label>
                                                                <input id="archivo" type="file" class="form-control" name="archivo" accept=".csv"/>
                                                            </div>
                                                            <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                <button type="submit" class="btn btn-primary w-md"><i class="mdi mdi-magnify"></i> Enviar</button>
                                                            </div>
                                                            <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                <a href="{{ asset('assets/templates/plantilla_chasis_motor.csv') }}" class="btn btn-success w-md" download>
                                                                    <i class="mdi mdi-download"></i> Descargar plantilla Chasis/Motor
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <div class="tab-pane fade" id="v-pills-number" role="tabpanel">
                                                    <form id="filterTemplateNumDispositivo" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="row">
                                                            <input type="hidden" id="inputNumList" name="numList"/>
                                                            <div class="col">
                                                                <label class="col-form-label fw-bold">Archivo</label>
                                                                <input id="archivoNum" type="file" class="form-control" name="archivoNum" accept=".csv"/>
                                                            </div>
                                                            <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                <button type="submit" class="btn btn-primary w-md"><i class="mdi mdi-magnify"></i> Enviar</button>
                                                            </div>
                                                            <div class="col-sm-3 col-lg-auto col-xl-auto text-right" style="text-align: right;padding-top:30px;">
                                                                <a href="{{ asset('assets/templates/plantilla_numeros.csv') }}" class="btn btn-success w-md" download>
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
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3: PROGRAMATION --}}
                            <div class="tab-pane" id="programation">
                                <form id="programationfrm" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col"><h4 class="card-title mt-2 mb-0" style="font-weight: 800;">Programación</h4></div>
                                        <div class="col-12"><hr class="mt-2 mb-2"></div>
                                        <div class="col-12">
                                            <div class="row border rounded p-3 m-3 mb-3">
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="program">Tipo de Programación de envío</label>
                                                        <select class="form-select" id="program" name="program" required>
                                                            <optgroup label="Notificación Única">
                                                                <option value="0" selected>Ahora</option>
                                                                <option value="1">Programado</option>
                                                            </optgroup>
                                                            <optgroup label="Notificaciones Recurrentes">
                                                                <option value="2">Diariamente</option>
                                                                <option value="3">Personalizar</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="schedule-once">
                                                    <div class="row p-3">
                                                        <div class="col-12 p-0"><hr class="m-0 mb-4"></div>
                                                        <div class="col-md-6 ps-0">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="schedule_date">Fecha de envío</label>
                                                                <input type="date" class="form-control" id="schedule_date" name="schedule_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="schedule_time">Hora de envío</label>
                                                                <input type="time" class="form-control" id="schedule_time" name="schedule_time" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 ps-0">
                                                            <small class="text-muted">La notificación se enviará una sola vez en la fecha y hora seleccionadas.</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12 d-none" id="schedule-daily">
                                                    <div class="row p-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label" for="schedule_daily_time">Hora diaria</label>
                                                            <input type="time" class="form-control" id="schedule_daily_time" name="schedule_daily_time" step="60">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label" for="schedule_daily_start">Fecha inicio</label>
                                                            <input type="date" class="form-control" id="schedule_daily_start" name="schedule_daily_start">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label" for="schedule_daily_end">Fecha fin</label>
                                                            <input type="date" class="form-control" id="schedule_daily_end" name="schedule_daily_end">
                                                        </div>
                                                        <div class="col-12">
                                                            <small class="text-muted d-block mt-2">Se enviará todos los días a la hora indicada dentro del rango de fechas.</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12 d-none" id="schedule-custom">
                                                    <div class="row p-3">
                                                        <div class="col-12 mb-3">
                                                            <label class="form-label" for="custom_type">Tipo de repetición</label>
                                                            <select class="form-select" id="custom_type" name="custom_type">
                                                                <option value="">Seleccione</option>
                                                                <option value="every_n_days">Cada N días</option>
                                                                <option value="weekly">Semanal</option>
                                                                <option value="monthly" disabled>Mensual (próximamente)</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-12 d-none" id="custom-every-n-days">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_every_n">Cada</label>
                                                                    <input type="number" min="1" class="form-control" id="custom_every_n" name="custom_every_n">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_time_n">Hora</label>
                                                                    <input type="time" class="form-control" id="custom_time_n" name="custom_time_n">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_start_n">Fecha inicio</label>
                                                                    <input type="date" class="form-control" id="custom_start_n" name="custom_start_n">
                                                                </div>
                                                                <div class="col-12 mt-2"><small class="text-muted">Se enviará cada <b>N</b> días, desde la fecha inicio, a la hora indicada.</small></div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 d-none" id="custom-weekly">
                                                            <div class="row">
                                                                <div class="col-12 mb-2">
                                                                    <label class="form-label">Días de la semana</label>
                                                                    <div class="d-flex flex-wrap gap-3">
                                                                        <label class="m-0"><input type="checkbox" value="1" name="custom_week_day[]" class="custom_week_day"> Lun</label>
                                                                        <label class="m-0"><input type="checkbox" value="2" name="custom_week_day[]" class="custom_week_day"> Mar</label>
                                                                        <label class="m-0"><input type="checkbox" value="3" name="custom_week_day[]" class="custom_week_day"> Mié</label>
                                                                        <label class="m-0"><input type="checkbox" value="4" name="custom_week_day[]" class="custom_week_day"> Jue</label>
                                                                        <label class="m-0"><input type="checkbox" value="5" name="custom_week_day[]" class="custom_week_day"> Vie</label>
                                                                        <label class="m-0"><input type="checkbox" value="6" name="custom_week_day[]" class="custom_week_day"> Sáb</label>
                                                                        <label class="m-0"><input type="checkbox" value="0" name="custom_week_day[]" class="custom_week_day"> Dom</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_week_time">Hora</label>
                                                                    <input type="time" class="form-control" id="custom_week_time" name="custom_week_time">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_week_start">Fecha inicio</label>
                                                                    <input type="date" class="form-control" id="custom_week_start" name="custom_week_start">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_week_end">Fecha fin (opcional)</label>
                                                                    <input type="date" class="form-control" id="custom_week_end" name="custom_week_end">
                                                                </div>
                                                                <div class="col-12 mt-2"><small class="text-muted">Se enviará en los días seleccionados a la hora indicada (hasta la fecha fin si la defines).</small></div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 d-none" id="custom-monthly">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_month_day">Día del mes</label>
                                                                    <input type="number" min="1" max="31" class="form-control" id="custom_month_day" name="custom_month_day">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_month_time">Hora</label>
                                                                    <input type="time" class="form-control" id="custom_month_time" name="custom_month_time">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label" for="custom_month_start">Fecha inicio</label>
                                                                    <input type="date" class="form-control" id="custom_month_start" name="custom_month_start">
                                                                </div>
                                                                <div class="col-12 mt-2"><small class="text-muted">Se enviará cada mes el día indicado a la hora indicada, desde la fecha inicio.</small></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- STEP 4: REVISION --}}
                            <div class="tab-pane" id="revision">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <div class="text-center">
                                            <div><h5 class="mb-5">Revisión Mensaje</h5></div>
                                            <div id="rev_content mt-2">
                                                <div class="row">
                                                    <div class="col-6 pe-4">
                                                        <div class="rev_content_visual">
                                                            <div id="accordion-visualization2" class="custom-accordion"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 ps-4">
                                                        <form id="NotificationSendfrm" method="POST">
                                                            @csrf
                                                            <input type="hidden" id="programation_h"        name="programation_h">
                                                            <input type="hidden" id="schedule_date_h"       name="schedule_date_h">
                                                            <input type="hidden" id="schedule_time_h"       name="schedule_time_h">
                                                            <input type="hidden" id="schedule_daily_time_h"  name="schedule_daily_time_h">
                                                            <input type="hidden" id="schedule_daily_start_h" name="schedule_daily_start_h">
                                                            <input type="hidden" id="schedule_daily_end_h"   name="schedule_daily_end_h">
                                                            <input type="hidden" id="custom_rule_json_h"     name="custom_rule_json_h">
                                                            <input type="hidden" id="titleCampaing_h"        name="titleCampaing_h">
                                                            <input type="hidden" id="subTitleCampaing_h"     name="subTitleCampaing_h">
                                                        </form>

                                                        <ul class="list-unstyled activity-wid">
                                                            <li class="activity-list">
                                                                <div class="activity-icon avatar-xs">
                                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><i class="mdi mdi-card-text-outline"></i></span>
                                                                </div>
                                                                <div class="text-left">
                                                                    <div><h5 class="font-size-13 mb-1">Contenido de la Aplicación</h5></div>
                                                                    <div><p class="text-muted mb-0 subtitle_input_text"></p></div>
                                                                </div>
                                                            </li>
                                                            <li class="activity-list">
                                                                <div class="activity-icon avatar-xs">
                                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><i class="mdi mdi-devices"></i></span>
                                                                </div>
                                                                <div class="text-left">
                                                                    <div><h5 class="font-size-13 mb-1">Audiencia Objetivo</h5></div>
                                                                    <div><p class="text-muted mb-0 count_datatable"></p></div>
                                                                </div>
                                                            </li>
                                                            <li class="activity-list">
                                                                <div class="activity-icon avatar-xs">
                                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><i class="mdi mdi-timetable"></i></span>
                                                                </div>
                                                                <div class="text-left">
                                                                    <div><h5 class="font-size-13 mb-1">Programación</h5></div>
                                                                    <div><p class="text-muted mb-0 programacion_text"></p></div>
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
                            <li class="previous_custom disabled"><a href="javascript: void(0);">Anterior</a></li>
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

@push('scripts')
<script>
    var tableData;
    window.__HMNOTIFY_APP_ID = @json(session('AppNotify.idLocal'));
    // Endpoints expuestos por HMNotify (Laravel 12) - proxy hacia HMSrvAuth
    var postDispositivos            = "{{ route('api.dispositivos-alt') }}";
    var postDispositivosSendNew2    = "{{ route('wizard.send') }}";
    var postDispoTemplateSendNew    = "{{ route('wizard.template-send') }}";
    var postDispoTemplateNumSendNew = "{{ route('wizard.template-num-send') }}";
</script>
<script src="{{ asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-libs/Select-2.0.0/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-libs/Select-2.0.0/js/select.bootstrap4.min.js') }}"></script>

<script src="{{ asset('assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
<script src="{{ asset('assets/libs/twitter-bootstrap-wizard/prettify.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('assets/libs/spectrum-colorpicker2/spectrum.min.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

@php
    $jsVer = filemtime(public_path('assets/js/pages/form-advanced2.init.js'));
@endphp
<script src="{{ asset('assets/js/pages/form-wizard.init.js') }}?v={{ $jsVer }}"></script>
<script src="{{ asset('assets/js/pages/form-advanced2.init.js') }}?v={{ $jsVer }}"></script>
<script src="{{ asset('assets/js/notificationTable.init.js') }}?v={{ $jsVer }}"></script>

<script src="{{ asset('assets/js/pages/multiple-select.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/datatableDispositivos.init.js') }}?v={{ $jsVer }}"></script>
@endpush
