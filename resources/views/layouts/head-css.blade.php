    @php $favicon = session('AppNotify.favicon') ?: asset('assets/images/favicon.ico'); @endphp
    <link rel="shortcut icon" href="{{ $favicon }}">
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">

    <!-- Bootstrap 5 (Nazox) -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">

    @stack('css')
