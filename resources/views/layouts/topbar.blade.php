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
<style>
    .navbar-brand-box {
        padding: 0 1rem !important;
    }
</style>
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="{{ $appName }}" height="45"></span>
                    <span class="logo-lg"><img src="{{ $logoLg }}" alt="{{ $appName }}" height="50" style="margin-left: 25px;"></span>
                </a>
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="{{ $appName }}" height="45"></span>
                    <span class="logo-lg"><img src="{{ $logoLg }}" alt="{{ $appName }}" height="50" style="margin-left: 25px;"></span>
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

            {{-- App Launcher SSO (waffle) - apps a las que el usuario tiene acceso --}}
            <x-sso-app-launcher />

            {{-- Account Menu - avatar con iniciales + dropdown estilo Google.
                 El item "Cambiar app" (selector multi-tenant de Notify) vive
                 DENTRO del dropdown del avatar, arriba de "Cerrar sesion".
                 Color dinamico: si la app de Notify tiene color lo usamos; sino azul Nazox. --}}
            @php
                $ssoMenuColor = session('AppNotify.color') ?: '#556ee6';
                $ssoMenuProfileUrl = rtrim(config('services.core_sso.base_uri', 'https://auth.24hm.net'), '/').'/profile';
            @endphp
            <x-sso-account-menu :color="$ssoMenuColor" :profile-url="$ssoMenuProfileUrl" />
        </div>
    </div>
</header>
