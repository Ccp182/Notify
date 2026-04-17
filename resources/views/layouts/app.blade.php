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
    <meta content="Hunter Monitoreo" name="author">
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
        .bg-primary-subtle  { background-color: rgba({{ hex2rgbStr($appColor) }}, 0.18) !important; }
        .text-bg-primary    { background-color: {{ $appColor }} !important; color: #fff !important; }
        .border-primary     { border-color: {{ $appColor }} !important; }
        a                   { color: {{ $appColor }}; }
        a:hover             { color: {{ $appColor }}; filter: brightness(0.85); }
        /* Twitter BS wizard: circulos de paso */
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.active .step-number,
        .twitter-bs-wizard .twitter-bs-wizard-nav .nav-link.done .step-number {
            background-color: {{ $appColor }} !important;
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
