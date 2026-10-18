<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sin acceso | Notify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Notify - Hunter Monitoreo" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.head-css')
</head>

{{--
  Pantalla "Sin acceso" de Notify (2026-10-17). Copia de la de HMSeshat
  (resources/views/acceso/sin-permiso.blade.php, DEVPX-4016, diseño de Daysi
  "Seshat Sin Acceso 1a"): mismo diseño y mismos textos, con "Notify"
  en lugar de "Seshat". Si cambia el diseño, se cambia en las dos.

  Página completa SIN menú a propósito: quien llega aquí no puede usar ninguna
  sección, y un menú con enlaces que devuelven este mismo mensaje solo confunde.

  Hoy la muestra SOLO el middleware AccesoNotify, con el motivo
  SUBUSUARIO_NO_SOPORTADO: Notify es solo para el usuario principal.
  `$motivo` SOLO elige el caso; nunca se imprime como HTML. Cualquier otro motivo
  cae en "No pudimos validar tu sesión" (cerrar sesión e ingresar de nuevo).

  El portal sale de services.core_sso.base_uri (destino fijo, nada del request).
  El contacto es el de Hunter del país de la sesión (config acceso.contacto_pais)
  y la razón social del pie, la de los PDF (reportes_pdf.razon_social). Sin país
  conocido, la tarjeta de contacto no sale.
--}}
@php
    $motivo = (string) ($motivo ?? '');
    $caso = match (true) {
        in_array($motivo, ['SUBUSUARIO_NO_SOPORTADO', 'SUBUSUARIO_SIN_PERMISO_APP'], true) => 'subusuario',
        default => 'sesion_invalida',
    };
    $casos = [
        'subusuario' => [
            'icono'   => 'ri-user-shared-line',
            'titulo'  => 'Ingresa con el usuario principal',
            'mensaje' => 'Notify es solo para el usuario principal de la cuenta. Ingresa con el usuario principal.',
            'otras'   => true,
        ],
        'sesion_invalida' => [
            'icono'   => 'ri-error-warning-line',
            'titulo'  => 'No pudimos validar tu sesión',
            'mensaje' => 'Por seguridad necesitamos que vuelvas a ingresar. Cierra sesión e inicia de nuevo desde el portal.',
            'otras'   => false,
        ],
    ];
    $c = $casos[$caso];

    $urlPortal = rtrim((string) config('services.core_sso.base_uri', ''), '/');
    $conPortal = $c['otras'] && str_starts_with($urlPortal, 'https://');

    $pais     = strtoupper((string) ($pais ?? session('Pais', '')));
    $contacto = config('acceso.contacto_pais.' . $pais);
    $razon    = (string) config('reportes_pdf.razon_social.' . $pais, '');
@endphp
<body class="auth-body-bg sk-sin-acceso">

    <style>
        /* Ajustes Hunter del modelo de Daysi (lo demás ya lo trae Nazox). */
        .sk-sin-acceso { background: #f1f5f7; }
        .sk-sin-acceso a { color: #C90F30; }
        .sk-sin-acceso a:hover { color: #A30C27; }
        .sk-sin-acceso .card { border: 0; border-radius: 14px; box-shadow: 0 2px 4px rgba(15, 34, 58, .12); }
        .sk-sin-acceso .avatar-lg { width: 6rem; height: 6rem; }
        .sk-sin-acceso .avatar-title { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        .sk-sin-acceso .sk-icono { background: #fde7eb; color: #C90F30; font-size: 32px; }
        .sk-sin-acceso .text-primary { color: #C90F30 !important; }
        .sk-sin-acceso .btn { font-weight: 500; padding: .6rem 1.1rem; border-radius: 8px; }
        .sk-sin-acceso .btn-primary { background: #C90F30; border-color: #C90F30; color: #fff; }
        .sk-sin-acceso .btn-primary:hover,
        .sk-sin-acceso .btn-primary:active { background: #A30C27 !important; border-color: #A30C27 !important; color: #fff; }
        .sk-sin-acceso .btn-outline-secondary { color: #495057; border-color: #adb5bd; }
        .sk-sin-acceso .btn-outline-secondary:hover { background: #495057; border-color: #495057; color: #fff; }
        .sk-sin-acceso .btn:focus-visible,
        .sk-sin-acceso a:focus-visible { outline: 3px solid #343a40; outline-offset: 2px; box-shadow: 0 0 0 5px rgba(240, 22, 62, .25) !important; }
        .sk-sin-acceso .contact-link { color: #343a40; font-weight: 500; text-decoration: none; }
        .sk-sin-acceso .contact-link:hover { color: #C90F30; text-decoration: underline; }
        .sk-sin-acceso .logo-auth { height: 56px; width: auto; transform: translateX(5px); }
        .sk-sin-acceso .min-w-0 { min-width: 0; }
    </style>

    <div class="account-pages d-flex align-items-center min-vh-100 py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="text-center mb-4">
                        @if ($urlPortal !== '' && str_starts_with($urlPortal, 'https://'))
                            <a href="{{ $urlPortal }}/" class="d-inline-block" aria-label="Hunter · Ir al portal">
                                <img src="{{ asset('assets/images/logo-hunter-red-dark.png') }}" alt="Hunter" class="logo-auth">
                            </a>
                        @else
                            <img src="{{ asset('assets/images/logo-hunter-red-dark.png') }}" alt="Hunter" class="logo-auth">
                        @endif
                    </div>

                    <div class="card overflow-hidden">
                        <div class="card-body p-4 text-center">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title rounded-circle sk-icono">
                                    <i class="{{ $c['icono'] }}" aria-hidden="true"></i>
                                </div>
                            </div>
                            <h1 class="font-size-20 fw-semibold mb-2">{{ $c['titulo'] }}</h1>
                            <p class="text-muted mb-4">{{ $c['mensaje'] }}</p>

                            @if ($c['otras'])
                                <p class="font-size-13 bg-light rounded-3 p-2 mb-4">
                                    <i class="ri-apps-2-line align-middle me-1 text-primary" aria-hidden="true"></i>Tu usuario sí tiene acceso a otras aplicaciones de Hunter.
                                </p>
                            @endif

                            <div class="d-grid gap-2">
                                @if ($conPortal)
                                    <a href="{{ $urlPortal }}/" class="btn btn-primary waves-effect waves-light">Ir al portal</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="d-grid m-0">
                                    @csrf
                                    <button type="submit" class="btn {{ $conPortal ? 'btn-outline-secondary' : 'btn-primary' }} waves-effect">Cerrar sesión</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if (is_array($contacto))
                        <div class="card overflow-hidden mt-3 mb-0">
                            <div class="card-body p-4">
                                <div class="text-start">
                                    <p class="fw-semibold font-size-13 mb-3">¿Necesitas ayuda? Contáctanos</p>
                                    <div class="row g-3 font-size-13">
                                        <div class="col-sm-6 d-flex gap-2">
                                            <i class="ri-phone-line text-primary font-size-16" aria-hidden="true"></i>
                                            <div><div class="text-muted">Atención al cliente</div><a href="{{ $contacto['telefono']['href'] }}" class="contact-link">{{ $contacto['telefono']['texto'] }}</a></div>
                                        </div>
                                        <div class="col-sm-6 d-flex gap-2">
                                            <i class="ri-time-line text-primary font-size-16" aria-hidden="true"></i>
                                            <div><div class="text-muted">Horario</div><div class="fw-medium">{{ $contacto['horario'] }}</div></div>
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <i class="ri-tools-line text-primary font-size-16" aria-hidden="true"></i>
                                            <div class="min-w-0"><div class="text-muted">Soporte técnico</div><a href="{{ $contacto['soporte']['href'] }}" class="contact-link text-nowrap">{{ $contacto['soporte']['texto'] }}</a></div>
                                        </div>
                                        @if (($contacto['correo'] ?? '') !== '')
                                            <div class="col-12 d-flex gap-2">
                                                <i class="ri-mail-line text-primary font-size-16" aria-hidden="true"></i>
                                                <div class="min-w-0"><div class="text-muted">Atención al cliente</div><a href="mailto:{{ $contacto['correo'] }}" class="contact-link text-nowrap">{{ $contacto['correo'] }}</a></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <p class="text-center text-muted font-size-13 mt-4 mb-0">© {{ date('Y') }} Todos los derechos reservados{{ $razon !== '' ? ' para ' . $razon : '' }}</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
