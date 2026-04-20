@php
    $appName      = session('AppNotify.name',  'HMNotify');
    $appLogoWhite = session('AppNotify.logo_white');
    $appIsoWhite  = session('AppNotify.isotype_white');
    $appLogo      = session('AppNotify.logo');
    $fallbackSm   = asset('assets/images/logo-sm-light.png');
    $fallbackLg   = asset('assets/images/logo-light.png');
    $logoSm       = $appIsoWhite  ?: ($appLogoWhite ?: ($appLogo ?: $fallbackSm));
    $logoLg       = $appLogoWhite ?: ($appLogo ?: $fallbackLg);
@endphp
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="{{ $appName }}" height="22"></span>
                    <span class="logo-lg"><img src="{{ $logoLg }}" alt="{{ $appName }}" height="28"></span>
                </a>
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="{{ $appName }}" height="22"></span>
                    <span class="logo-lg"><img src="{{ $logoLg }}" alt="{{ $appName }}" height="28"></span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="ri-menu-2-line align-middle"></i>
            </button>

            <div class="d-none d-md-flex align-items-center ms-3 text-muted">
                <small>{{ $appName }} &middot; {{ session('Pais', 'EC') }}</small>
            </div>
        </div>

        <div class="d-flex">
            {{-- Bandera del país OAuth (routing country) --}}
            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item waves-effect" title="{{ session('Pais', 'EC') }}">
                    <img src="https://flagsapi.com/{{ session('Pais', 'EC') }}/flat/32.png"
                         alt="{{ session('Pais', 'EC') }}" height="28">
                </button>
            </div>

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown">
                    <img class="rounded-circle header-profile-user me-1" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="">
                    <span class="d-none d-xl-inline-block ms-1">{{ session('FName', session('User', 'Usuario')) }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('select-app.show') }}">
                        <i class="ri-apps-2-line align-middle me-1"></i> Cambiar app
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ri-shut-down-line align-middle me-1 text-danger"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
