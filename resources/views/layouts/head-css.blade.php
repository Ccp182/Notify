    @php
        // Favicon: app seleccionada -> favicon de la app; si no -> logo HMNotify.
        // Evita heredar el favicon generico de Nazox (public/assets/images/favicon.ico)
        // que es el que trae el template y luce como HMSSO.
        $favicon = session('AppNotify.favicon') ?: 'https://res.24hm.net/General/HunterMonitoreo/Apps/favicon.ico';
    @endphp
    <link rel="shortcut icon" href="{{ $favicon }}">
    <link rel="icon" href="{{ $favicon }}">

    <!-- Bootstrap 5 (Nazox) -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <!-- MDI 7.4.47 (HX-15): despues de icons de Nazox -->
    <link href="{{ asset('assets/libs/mdi7/css/materialdesignicons.min.css') }}" rel="stylesheet" type="text/css">
    <!-- hm-icons: set propio (hmi hmi-x) -->
    <link href="{{ asset('assets/libs/hm-icons/hm-icons.css') }}" rel="stylesheet" type="text/css">
    <!-- hm-motion: movimiento sutil comun (reduced-motion incluido) -->
    <link href="{{ asset('assets/libs/hm-motion/hm-motion.css') }}?v=2" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

    @stack('css')
