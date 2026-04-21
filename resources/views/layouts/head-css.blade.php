    @php
        // Favicon: app seleccionada -> favicon de la app; si no -> logo HMNotify.
        // Evita heredar el favicon generico de Nazox (public/assets/images/favicon.ico)
        // que es el que trae el template y luce como HMSSO.
        $favicon = session('AppNotify.favicon') ?: asset('assets/images/logo-sm-dark.png');
    @endphp
    <link rel="shortcut icon" href="{{ $favicon }}">
    <link rel="icon" href="{{ $favicon }}">

    <!-- Bootstrap 5 (Nazox) -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

    @stack('css')
