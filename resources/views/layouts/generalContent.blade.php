<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Hunter Monitoreo Notify @yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Administrador de Notificaciones" name="description" />
        <meta content="Desarrollador" name="Carlos Carpio Paredes" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @include('layouts.head')
        <link href="{{ asset('assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ $overview['CSS']}}" rel="stylesheet" />
    </head>

    <body data-sidebar="dark" data-keep-enlarged="true" class="vertical-collpsed">
    <div id="preloader">
        <div class="preloader-wrap">
            <img src="{{ $overview['LOGO'] }}" alt="logo" class="img-fluid" />
            <div class="thecube">
                <div class="cube c1"></div>
                <div class="cube c2"></div>
                <div class="cube c4"></div>
                <div class="cube c3"></div>
            </div>
        </div>
    </div>
    <style>
        .main-content .content {
            margin-top: 0px; 
        }
        body[data-sidebar=dark] .navbar-brand-box {
            background: #DD0330 !important;
        }
        .navbar-brand-box {
            padding: 0 0px;
            width: 240px;
            text-align: center;
        }
        body[data-sidebar=dark] .navbar-brand-box {
            background:  {{ $overview['COLOR'] }}!important;
        }
    </style>
    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">

            @include('layouts.topbar')
            
            @include('layouts.sidebar')
            
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                @include('layouts.footer')
            </div>
            <!-- end main content-->
        </div>
        <!-- END layout-wrapper -->

        <!-- Right Sidebar -->
        @include('layouts.rightbar')
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- JAVASCRIPT -->
        @include('layouts.vendor-scripts')
        <script src="{{ asset('assets/libs/toastr/build/toastr.min.js') }}"></script>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            var userHM = @json(Session::get('UserHM'));
            var passHM = @json(Session::get('PassHM'));//@json(Cache::get('listGroupSG_'.Session::get('UserHM')));
        </script>
        @if (Session::has('ProxCad') && Session::get('ProxCad.Show'))
        <script>
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-bottom-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": 300,
                "hideDuration": 1000,
                "timeOut": 0,
                "extendedTimeOut": 0,
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                "tapToDismiss": false
            }
            var message = '{!! addslashes(Session::get('ProxCad.Message')) !!}';
            var expLink = '{!! addslashes(Session::get('ProxCad.ExpLink')) !!}';
            toastr.warning(message + ' (' + expLink + ')<br/><div style="margin-top:10px;"><button id="notiRedirectButton" class="btn btn-secondary">Procesar</button> <button id="notiOmitButton" class="btn btn-light">Omitir</button></div>', 'Credenciales Próximas a caducar');
            $("#notiRedirectButton").click(function() {
                window.location.href = "{{ Session::get('ProxCad.Link') }}";
            });

            $("#notiOmitButton").click(function() {
                $.ajax({
                    url: '/clearVarSesion',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        variableName: 'ProxCad'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.remove();
                        } else {
                            console.error('Error al eliminar la variable de sesión.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', status, error);
                    }
                });
                //toastr.remove();
            });
        </script>
        
        @endif
        <script>
            /*// Función para mostrar el mensaje al cargar la página
            function mostrarMensajeAlCargar() {
                // Verifica si el mensaje ya se ha cerrado anteriormente
                if (!localStorage.getItem('mensajeCerrado')) {
                    // Crea una instancia de Ushebtis
                    const ushebtis = new Ushebtis(27);// Crea una instancia con el código de aplicación 27(Gestor de Flotas) cambiar por el suyo
                    // Llama al método showMessage con el código de país (puedes cambiarlo según tus necesidades)
                    ushebtis.showMessage(@json(Session::get('Pais')).toLowerCase()); // Ejemplo con código de país 'ec' (Ecuador),'pe' (Perú),'co' (Colombia)
                }
            }
            // Registra la función para ejecutarse cuando la página se cargue completamente
            window.onload = mostrarMensajeAlCargar;

            countryApp=@json(strtolower(Session::get('Pais')));
            // Registra la función para ejecutarse cuando la página se cargue completamente
            window.onload = mostrarMensajeAlCargar;*/

            let countryApp = @json(strtolower(Session::get('Pais', 'EC')));
            let codeApp = {{ $codeApp ?? 59 }};
            window.onload = mostrarMensajeAlCargar;
        </script>
        @yield('script')

    </body>
</html>