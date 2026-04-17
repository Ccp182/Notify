@php
    $appColor = session('AppNotify.color', '#556ee6');
    $appName  = session('AppNotify.name',  'HMNotify');
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>HMNotify @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de notificaciones push - Hunter Monitoreo" name="description">
    <meta content="Hunter Monitoreo" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.head-css')

    <style>
        :root {
            --bs-primary:     {{ $appColor }};
            --bs-primary-rgb: {{ hex2rgbStr($appColor) }};
        }
        body[data-sidebar=dark] .navbar-brand-box { background: {{ $appColor }} !important; }
        .btn-primary { background-color: {{ $appColor }}; border-color: {{ $appColor }}; }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active { background-color: {{ $appColor }}; filter: brightness(0.9); }
        .text-primary { color: {{ $appColor }} !important; }
        .bg-primary   { background-color: {{ $appColor }} !important; }
        a { color: {{ $appColor }}; }
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
