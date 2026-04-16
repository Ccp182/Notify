
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ isset($overview['FAVICON']) ? $overview['FAVICON'] : 'https://am.24hm.net/assets/images/app/hmmovil/favicon.ico' }}">

    @yield('css')
    
    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />  
    <!-- Bootstrap Css -->
    <link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ URL::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <link href="https://res.24hm.net/Ushebtis/css/toastr.min.css" rel="stylesheet" type="text/css">

    <link href="https://res.24hm.net/Ushebtis/css/sweetalert2.min.css" rel="stylesheet" type="text/css">
    <link href="https://res.24hm.net/Ushebtis/css/Ushebtis.css" id="app-style" rel="stylesheet" type="text/css" />
    <!--<link href=" https://am.24hm.net/assets/css/main_HMonitoreo.css" id="app-style" rel="stylesheet" type="text/css" />-->