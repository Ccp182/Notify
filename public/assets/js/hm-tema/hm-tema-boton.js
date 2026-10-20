/*
 * hm-tema-boton.js — cablea el boton de tema del encabezado (HX-29).
 * Automatico (el del sistema) -> Claro -> Oscuro. El tema lo aplica hm-tema.js (window.HMTema).
 * Se carga al final del <body>. El boton:
 *   <button type="button" class="btn header-item noti-icon waves-effect" id="hm-tema-btn"
 *           title="Tema" aria-label="Cambiar tema"><i class="mdi mdi-theme-light-dark"></i></button>
 */
(function () {
    'use strict';
    var b = document.getElementById('hm-tema-btn');
    if (!b || !window.HMTema) { return; }
    var SIG = { auto: 'claro', claro: 'oscuro', oscuro: 'auto' };
    var TXT = { auto: 'Tema: automático (el del sistema)', claro: 'Tema: claro', oscuro: 'Tema: oscuro' };
    var ICO = { auto: 'mdi-theme-light-dark', claro: 'mdi-white-balance-sunny', oscuro: 'mdi-weather-night' };
    function pintar() {
        var t = window.HMTema.actual();
        b.title = TXT[t] + ' · clic para cambiar';
        b.setAttribute('aria-label', TXT[t] + '. Cambiar tema');
        var i = b.querySelector('i');
        if (i) { i.className = 'mdi ' + ICO[t]; }
    }
    b.addEventListener('click', function () {
        window.HMTema.fijar(SIG[window.HMTema.actual()] || 'auto');
        pintar();
    });
    pintar();
})();
