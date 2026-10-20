/*
 * hm-tema.js — tema claro / oscuro comun a las apps del SSO (HX-29, decision D4).
 *
 * Se carga SINCRONO en el <head>, despues de los <link> de bootstrap y app de Nazox,
 * para fijar el tema antes de pintar (sin parpadeo):
 *
 *   <link id="bootstrap-style" href=".../bootstrap.min.css"
 *         data-claro=".../bootstrap.min.css" data-oscuro=".../bootstrap-dark.min.css">
 *   <link id="app-style" href=".../app.min.css"
 *         data-claro=".../app.min.css" data-oscuro=".../app-dark.min.css">
 *   <script src=".../hm-tema.js" data-ids="bootstrap-style,app-style"
 *           data-evento="gf:tema" data-alias="GFTema" data-clave="gf.tema"></script>
 *
 * Preferencias: 'auto' (el del sistema) -> 'claro' -> 'oscuro'.
 * Se guarda en la cookie hm_tema del dominio .24hm.net, asi la eleccion sigue al usuario
 * de una app a otra; localStorage queda de respaldo (y para leer la clave vieja de la app).
 *
 * Marca <html data-tema="claro|oscuro" data-tema-pref="auto|claro|oscuro"> y dispara
 * 'hm:tema' (y el evento propio de la app, si se indica) con {tema, oscuro}.
 * API: window.HMTema.actual() / .oscuro() / .fijar(t); data-alias crea el mismo objeto
 * con el nombre que ya usaba la app (GFTema, AnubisTema).
 */
(function () {
    'use strict';

    var script = document.currentScript || {};
    var dato = function (n, d) { return (script.getAttribute && script.getAttribute('data-' + n)) || d; };

    var IDS     = dato('ids', 'bootstrap-style,app-style').split(',');
    var EVENTO  = dato('evento', '');
    var ALIAS   = dato('alias', '');
    var CLAVE   = dato('clave', 'hm.tema');   // localStorage (respaldo / compatibilidad)
    var VALIDOS = { auto: 1, claro: 1, oscuro: 1 };
    var COOKIE  = 'hm_tema';

    var dominio = /(^|\.)24hm\.net$/i.test(location.hostname) ? '; domain=.24hm.net' : '';

    function leerCookie() {
        var m = document.cookie.match(/(?:^|;\s*)hm_tema=(auto|claro|oscuro)(?:;|$)/);
        return m ? m[1] : null;
    }

    function guardar(t) {
        try {
            document.cookie = COOKIE + '=' + t + dominio + '; path=/; max-age=31536000; SameSite=Lax'
                + (location.protocol === 'https:' ? '; Secure' : '');
        } catch (e) { /* sin cookies */ }
        try { localStorage.setItem(CLAVE, t); } catch (e) { /* sin almacenamiento */ }
    }

    var tema = leerCookie();
    if (!tema) {
        try { tema = localStorage.getItem(CLAVE); } catch (e) { tema = null; }
    }
    if (!VALIDOS[tema]) { tema = 'auto'; }

    var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function aplicar() {
        var oscuro = tema === 'oscuro' || (tema === 'auto' && !!(mq && mq.matches));
        var raiz = document.documentElement;
        raiz.setAttribute('data-tema', oscuro ? 'oscuro' : 'claro');
        raiz.setAttribute('data-tema-pref', tema);
        IDS.forEach(function (id) {
            var l = document.getElementById(id.trim());
            var href = l && l.getAttribute(oscuro ? 'data-oscuro' : 'data-claro');
            if (l && href && l.getAttribute('href') !== href) { l.setAttribute('href', href); }
        });
        var detalle = { detail: { tema: tema, oscuro: oscuro } };
        document.dispatchEvent(new CustomEvent('hm:tema', detalle));
        if (EVENTO) { document.dispatchEvent(new CustomEvent(EVENTO, detalle)); }
    }

    if (mq) {
        var alCambiar = function () { if (tema === 'auto') { aplicar(); } };
        if (mq.addEventListener) { mq.addEventListener('change', alCambiar); }
        else if (mq.addListener) { mq.addListener(alCambiar); }
    }

    var api = {
        actual: function () { return tema; },
        oscuro: function () { return document.documentElement.getAttribute('data-tema') === 'oscuro'; },
        fijar: function (t) {
            tema = VALIDOS[t] ? t : 'auto';
            guardar(tema);
            aplicar();
        }
    };

    window.HMTema = api;
    if (ALIAS) { window[ALIAS] = api; }

    aplicar();
})();
