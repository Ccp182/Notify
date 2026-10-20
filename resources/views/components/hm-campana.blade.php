{{--
    Campana de avisos del encabezado (HX-29, decision D4). Comun a las apps del SSO.
    Se incluye en el topbar, junto al boton de tema:  @include('components.hm-campana', ['url' => url('/avisos')])
    La ruta propia de la app (/avisos) pide GET /api/{pais}/AuthSSO/avisos?app=<App> al SSO con el
    token de la sesion y devuelve el JSON tal cual. La logica esta en assets/js/hm-campana/hm-campana.js.
    Icono: campana Tabler rellena (bell-filled, licencia MIT), en SVG para no cargar la libreria.
--}}
<div class="dropdown d-inline-block ms-1">
    <button type="button" class="btn header-item noti-icon waves-effect position-relative" id="hm-campana-btn"
            data-url="{{ $url ?? url('/avisos') }}" data-bs-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false" title="Avisos" aria-label="Avisos">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M14.235 19c.865 0 1.322 1.024 .745 1.668a3.992 3.992 0 0 1 -2.98 1.332a3.992 3.992 0 0 1 -2.98 -1.332c-.552 -.616 -.158 -1.579 .634 -1.661l.11 -.006h4.471z"/>
            <path d="M12 2c1.358 0 2.506 .903 2.875 2.141l.046 .171l.008 .043a8.013 8.013 0 0 1 4.024 6.069l.028 .287l.019 .289v2.931l.021 .136a3 3 0 0 0 1.143 1.847l.167 .117l.162 .099c.86 .487 .56 1.766 -.377 1.864l-.116 .006h-16c-1.028 0 -1.387 -1.364 -.493 -1.87a3 3 0 0 0 1.472 -2.063l.021 -.143l.001 -2.97a8 8 0 0 1 3.821 -6.454l.248 -.146l.01 -.043a3.003 3.003 0 0 1 2.562 -2.29l.182 -.017l.176 -.004z"/>
        </svg>
        <span class="noti-dot" id="hm-campana-dot" style="display: none;"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="hm-campana-btn">
        <div class="p-3 border-bottom">
            <h6 class="m-0">Avisos</h6>
        </div>
        <div id="hm-campana-lista" style="max-height: 320px; overflow-y: auto;" aria-live="polite"></div>
    </div>
</div>
