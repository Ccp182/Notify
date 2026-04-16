<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Hunter Monitoreo Notify - @yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Hunter Monitoreo Notify" name="Administración de notificaciones" />
        <meta content="Desarrollador" name="Carlos Carpio Paredes" />

        @include('layouts.head')
        <link href="{{ URL::asset('assets/css/3.3.1_css_bootstrap.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        <link href="{{ URL::asset('assets/css/flags.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        
    </head>

    <body class="auth-body-bg">
        <div>
            <div class="container-fluid p-0">
                <div class="row g-0">
                    <div class="col-lg-4">
                        @yield('content')
                    </div>
                    <div class="col-lg-8">
                        <div class="authentication-bg position-relative">
                            <div class="bg-overlay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        @if (session('ErrorRedirectLink'))
            <script>
                Swal.fire({
                    title: "{{session('ErrorTitle')}}",
                    text: "{{session('ErrorText')}}",
                    allowOutsideClick: true,
                    icon: "{{session('ErrorIcon')}}",
                    confirmButtonColor: "#d25656",
                    confirmButtonText: 'Procesar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{session('ErrorRedirectLink')}}";
                    }
                });

                
            </script>
        @endif
        @include('layouts.vendor-scripts')
        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script src="{{ asset('assets/js/pages/jquery.flagstrap.min.js') }}"></script>
        <script>
            $('#opt_country').flagStrap({
                countries: {
                    "EC": "Ecuador",
                    "PE": "Perú",
                    "CO": "Colombia",
                    "CH": "Chile",
                    "MX": "México",
                    "PA": "Panamá"
                },
                buttonSize: "btn-sm",
                buttonType: "btn-light",
                labelMargin: "10px",
                scrollable: false,
                scrollableHeight: "350px",
                onSelect: function (value, element) {
                    $('#country').val(value);
                    countryApp=value.toLowerCase();
                    mostrarMensajeAlCargar();
                }
            });

        </script>
        <script>
            $(document).ready(function() {
                $("#loginfrm2").submit();
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#loginfrm').on('submit', function() {
                    $('#loading').show(); // Mostrar el indicador de carga
                });
            });
        </script>

        <script>
            /*function mostrarMensajeAlCargar() {
                if (!localStorage.getItem('mensajeCerrado')) {
                    const ushebtis = new Ushebtis(27);// Crea una instancia con el código de aplicación 27(Gestor de Flotas) cambiar por el suyo
                    ushebtis.showMessage('ec'); // Ejemplo con código de país 'ec' (Ecuador),'pe' (Perú),'co' (Colombia)
                }
            }
            window.onload = mostrarMensajeAlCargar;*/$
            let countryApp = @json(strtolower(Session::get('Pais', 'EC')));
            let codeApp = {{ $codeApp ?? 59 }};
            window.onload = mostrarMensajeAlCargar;
        </script>
    </body>
</html>
