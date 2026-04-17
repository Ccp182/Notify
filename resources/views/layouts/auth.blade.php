<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>HMNotify @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="HMNotify - Sistema de notificaciones Hunter Monitoreo" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.head-css')
</head>

<body class="authentication-bg authentication-bg-pattern">

    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-login text-center">
                            <div class="bg-login-overlay"></div>
                            <div class="position-relative">
                                <h5 class="text-white font-size-20">@yield('heading', 'HMNotify')</h5>
                                <p class="text-white-50 mb-0">@yield('subtitle', 'Sistema de notificaciones')</p>
                            </div>
                        </div>
                        <div class="card-body pt-5">
                            @yield('card-body')
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <p>{{ date('Y') }} &copy; Hunter Monitoreo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.vendor-scripts')
</body>
</html>
