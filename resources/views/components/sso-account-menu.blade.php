{{--
    Componente SSO Account Menu (estilo Google).
    Avatar con iniciales + dropdown con nombre, email y "Gestionar mi cuenta".
    Ver skill hm-sso-ecosystem para detalles.
--}}
@props([
    'color'       => '#556ee6',
    'profileUrl'  => 'https://auth.24hm.net/profile',
    'logoutRoute' => 'logout',
])

@php
    use Illuminate\Support\Facades\Session;

    $accFName = Session::get('FName', 'Usuario');
    $accMail  = Session::get('Mail', '');
    $accParts = array_values(array_filter(preg_split('/\s+/', trim($accFName ?? '')) ?: []));

    if (empty($accParts)) {
        $accInitials = '?';
    } elseif (count($accParts) === 1) {
        $accInitials = mb_strtoupper(mb_substr($accParts[0], 0, 1));
    } else {
        $accInitials = mb_strtoupper(mb_substr($accParts[0], 0, 1) . mb_substr($accParts[1], 0, 1));
    }

    $accShade     = abs(crc32((string) $accFName)) % 10;
    $accFirstName = $accParts[0] ?? 'Usuario';
@endphp

@once
<style>
    .sso-avatar{display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:600;border-radius:50%;line-height:1;text-transform:uppercase;font-family:'Poppins',system-ui,sans-serif;box-shadow:0 1px 2px rgba(0,0,0,.1);overflow:hidden;vertical-align:middle;}
    .sso-avatar-sm{width:34px;height:34px;font-size:15px;}
    .sso-avatar-md{width:56px;height:56px;font-size:22px;}
    .sso-avatar-lg{width:64px;height:64px;font-size:26px;display:flex;}
    .sso-shade-0{filter:brightness(.78) saturate(1.1);}
    .sso-shade-1{filter:brightness(.85) saturate(1.05);}
    .sso-shade-2{filter:brightness(.92);}
    .sso-shade-3{filter:brightness(.98);}
    .sso-shade-4{filter:brightness(1.04);}
    .sso-shade-5{filter:brightness(1.10);}
    .sso-shade-6{filter:brightness(1.16) saturate(.95);}
    .sso-shade-7{filter:brightness(1.22) saturate(.90);}
    .sso-shade-8{filter:brightness(.88) hue-rotate(-8deg);}
    .sso-shade-9{filter:brightness(1.06) hue-rotate(8deg);}
    .sso-account-dropdown{width:280px;overflow:hidden;}
    .sso-account-header{background:linear-gradient(180deg,#f8f9fa 0%,#fff 100%);border-bottom:1px solid rgba(0,0,0,.05);}
    .sso-account-header h6{font-size:15px;margin-top:.25rem;}
    .sso-account-header .sso-avatar-lg{margin-left:auto;margin-right:auto;}
</style>
@endonce

<div class="dropdown d-inline-block user-dropdown sso-account-menu">
    <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="sso-avatar sso-avatar-sm sso-shade-{{ $accShade }}" style="background-color: {{ $color }};">{{ $accInitials }}</span>
        <span class="d-none d-xl-inline-block ms-1">{{ $accFName }}</span>
        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end sso-account-dropdown p-0">
        <div class="sso-account-header text-center p-3">
            <span class="sso-avatar sso-avatar-lg sso-shade-{{ $accShade }} mb-2" style="background-color: {{ $color }};">{{ $accInitials }}</span>
            <h6 class="mb-0 fw-bold">¡Hola, {{ $accFirstName }}!</h6>
            @if($accMail)
                <small class="text-muted d-block text-truncate">{{ $accMail }}</small>
            @endif
            <a href="{{ $profileUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mt-3 rounded-pill px-3">
                <i class="ri-user-settings-line align-middle me-1"></i> Gestionar mi cuenta
            </a>
        </div>
        <div class="dropdown-divider m-0"></div>
        {{-- Item especifico de HMNotify: cambiar la app activa de Notify
             (multi-tenant interno de Notify, NO del SSO). --}}
        <a class="dropdown-item py-2" href="{{ route('select-app.show') }}">
            <i class="ri-exchange-box-line align-middle me-1"></i> Cambiar app
        </a>
        <div class="dropdown-divider m-0"></div>
        <form method="POST" action="{{ route($logoutRoute) }}" class="m-0">
            @csrf
            <button type="submit" class="dropdown-item text-danger py-2">
                <i class="ri-shut-down-line align-middle me-1 text-danger"></i> Cerrar sesión
            </button>
        </form>
    </div>
</div>
