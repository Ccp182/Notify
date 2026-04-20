@php
    $appColor = config('app.primary_color') ?: session('AppNotify.color', '#556ee6');
    $appName  = session('AppNotify.name',  'HMNotify');
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>HMNotify @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de notificaciones push - Hunter Monitoreo" name="description">
    <meta content="Carlos Carpio Paredes" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.head-css')

    <style>
        :root {
            --bs-primary:               {{ $appColor }};
            --bs-primary-rgb:           {{ hex2rgbStr($appColor) }};
            --bs-link-color:            {{ $appColor }};
            --bs-link-hover-color:      {{ $appColor }};
            --bs-nav-pills-link-active-bg: {{ $appColor }};
        }
        body[data-sidebar=dark] .navbar-brand-box { background: {{ $appColor }} !important; }
        .btn-primary,
        .btn-primary:active,
        .btn-primary:focus,
        .btn-primary:hover  { background-color: {{ $appColor }} !important; border-color: {{ $appColor }} !important; }
        .btn-primary:hover  { filter: brightness(0.92); }
        .text-primary       { color: {{ $appColor }} !important; }
        .bg-primary         { background-color: {{ $appColor }} !important; }
        .nav-pills .nav-link.active,
        .nav-pills .show > .nav-link {
            background-color: {{ $appColor }} !important;
            color: #fff !important;
        }
        /* Twitter BS wizard: solo el circulo del paso toma color, el tab queda transparente */
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link,
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active,
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.done {
            background-color: transparent !important;
            color: inherit !important;
        }
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link .step-number {
            background-color: #ffffff !important;
            color: {{ $appColor }} !important;
            border: 2px solid {{ $appColor }} !important;
        }
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active .step-number,
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.done .step-number {
            background-color: {{ $appColor }} !important;
            color: #fff !important;
        }
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active .step-title,
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.done .step-title {
            color: {{ $appColor }} !important;
        }
        .bg-primary-subtle  { background-color: rgba({{ hex2rgbStr($appColor) }}, 0.18) !important; }
        .text-bg-primary    { background-color: {{ $appColor }} !important; color: #fff !important; }
        .border-primary     { border-color: {{ $appColor }} !important; }
        /* Nazox avatar-title usa color literal; forzar primary */
        .avatar-title              { background-color: {{ $appColor }} !important; color: #fff !important; }
        .bg-primary .avatar-title  { background-color: {{ $appColor }} !important; }
        .mini-stat-icon.bg-primary { background-color: {{ $appColor }} !important; }
        a                   { color: {{ $appColor }}; }
        a:hover             { color: {{ $appColor }}; filter: brightness(0.85); }
        .card               { border: 1px solid rgba(72, 94, 144, 0.16) !important; }

        /* ----- Paginacion DataTables / Bootstrap ----- */
        .pagination .page-link,
        .dataTables_paginate .paginate_button {
            color: {{ $appColor }} !important;
        }
        .pagination .page-link:hover,
        .dataTables_paginate .paginate_button:hover {
            color: #fff !important;
            background-color: {{ $appColor }} !important;
            border-color: {{ $appColor }} !important;
        }
        .pagination .page-item.active .page-link,
        .dataTables_paginate .paginate_button.current,
        .dataTables_paginate .paginate_button.current:hover {
            background-color: {{ $appColor }} !important;
            border-color: {{ $appColor }} !important;
            color: #fff !important;
        }
        .dataTables_paginate .paginate_button.disabled,
        .dataTables_paginate .paginate_button.disabled:hover {
            color: #adb5bd !important;
            background: transparent !important;
            border-color: #dee2e6 !important;
        }

        /* ----- Inputs/selects focus (neutro, no parece error) ----- */
        .form-control:focus,
        .form-select:focus {
            border-color: rgba(72, 94, 144, 0.35) !important;
            box-shadow: 0 0 0 .15rem rgba(72, 94, 144, 0.10) !important;
        }

        /* ----- Boton outline-primary ----- */
        .btn-outline-primary {
            color: {{ $appColor }} !important;
            border-color: {{ $appColor }} !important;
        }
        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: {{ $appColor }} !important;
            border-color: {{ $appColor }} !important;
            color: #fff !important;
        }
    </style>
</head>

<body data-sidebar="dark">

    <div id="layout-wrapper">

        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @include('layouts.page-title', [
                        'pagetitle' => 'HMNotify',
                        'title'     => $pageTitle ?? '',
                    ])

                    @yield('content')
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
</body>
</html>
