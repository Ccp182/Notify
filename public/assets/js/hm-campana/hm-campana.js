/*
 * hm-campana.js — campana de avisos del encabezado, comun a las apps del SSO (HX-29, D4).
 *
 * Fuente principal: los avisos del SSO (GET /api/{pais}/AuthSSO/avisos), que cada app
 * pide por SU servidor (ruta propia, con el token de la sesion). La URL la da el
 * atributo data-url del boton (#hm-campana-btn).
 *
 * Fuentes extra (Anubis suma las alertas de la flota): antes de cargar este archivo,
 *   window.HMCampanaFuentes = [ function () { return Promise.resolve([ aviso, ... ]); } ];
 * Cada aviso: { id, tipo, nivel: 'info'|'aviso'|'urgente', titulo, texto, enlace }.
 *
 * "Visto": ids en localStorage ('hm.avisos.vistos'); el punto rojo sale si hay alguno
 * sin ver y se apaga al abrir la lista. Todo el texto se inserta con textContent.
 * Refresca cada 5 minutos y al volver a la pestana.
 */
(function () {
    'use strict';

    var btn   = document.getElementById('hm-campana-btn');
    var lista = document.getElementById('hm-campana-lista');
    var punto = document.getElementById('hm-campana-dot');
    if (!btn || !lista) { return; }

    var URL_AVISOS = btn.getAttribute('data-url') || '';
    var CLAVE = 'hm.avisos.vistos';
    var REFRESCO = 5 * 60 * 1000;
    var actuales = [];

    var NIVEL = {
        urgente: { icono: 'mdi mdi-alert-circle', color: 'text-danger' },
        aviso:   { icono: 'mdi mdi-alert',        color: 'text-warning' },
        info:    { icono: 'mdi mdi-information',  color: 'text-info' }
    };
    var ORDEN = { urgente: 0, aviso: 1, info: 2 };

    function vistos() {
        try { return JSON.parse(localStorage.getItem(CLAVE) || '[]') || []; } catch (e) { return []; }
    }
    function guardarVistos(ids) {
        try { localStorage.setItem(CLAVE, JSON.stringify(ids.slice(-300))); } catch (e) { /* sin almacenamiento */ }
    }

    function pedirSSO() {
        if (!URL_AVISOS) { return Promise.resolve([]); }
        return fetch(URL_AVISOS, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : { Avisos: [] }; })
            .then(function (j) { return Array.isArray(j && j.Avisos) ? j.Avisos : []; })
            .catch(function () { return []; });
    }

    function fuentesExtra() {
        var fs = Array.isArray(window.HMCampanaFuentes) ? window.HMCampanaFuentes : [];
        return fs.map(function (f) {
            try {
                return Promise.resolve(f())
                    .then(function (a) { return Array.isArray(a) ? a : []; })
                    .catch(function () { return []; });
            } catch (e) { return Promise.resolve([]); }
        });
    }

    function el(tag, clase, texto) {
        var n = document.createElement(tag);
        if (clase) { n.className = clase; }
        if (texto !== undefined && texto !== null) { n.textContent = texto; }
        return n;
    }

    function actualizarPunto() {
        var yaVistos = vistos();
        var nuevos = actuales.filter(function (a) { return yaVistos.indexOf(String(a.id)) === -1; }).length;
        if (punto) { punto.style.display = nuevos ? '' : 'none'; }   // Nazox fija display en .noti-dot: hidden no basta
        btn.setAttribute('aria-label', nuevos ? 'Avisos: ' + nuevos + ' sin ver' : 'Avisos');
    }

    function pintar() {
        lista.textContent = '';
        if (!actuales.length) {
            var vacio = el('div', 'text-center text-muted p-4');
            vacio.appendChild(el('i', 'mdi mdi-bell-check-outline d-block font-size-24 mb-1'));
            vacio.appendChild(el('span', '', 'No tienes avisos'));
            lista.appendChild(vacio);
        }
        var yaVistos = vistos();
        actuales.forEach(function (a) {
            var n = NIVEL[a.nivel] || NIVEL.info;
            var item = el(a.enlace ? 'a' : 'div', 'text-reset notification-item d-block');
            if (a.enlace) { item.href = a.enlace; }
            var fila = el('div', 'd-flex p-3 border-bottom');
            var ico = el('div', 'flex-shrink-0 me-3');
            ico.appendChild(el('i', n.icono + ' ' + n.color + ' font-size-20'));
            var cuerpo = el('div', 'flex-grow-1');
            var tit = el('h6', 'mb-1 font-size-14', a.titulo || '');
            if (yaVistos.indexOf(String(a.id)) === -1) { tit.classList.add('fw-bold'); }
            cuerpo.appendChild(tit);
            if (a.texto) { cuerpo.appendChild(el('p', 'mb-0 font-size-13 text-muted', a.texto)); }
            fila.appendChild(ico);
            fila.appendChild(cuerpo);
            item.appendChild(fila);
            lista.appendChild(item);
        });
        actualizarPunto();
    }

    function cargar() {
        Promise.all([pedirSSO()].concat(fuentesExtra())).then(function (partes) {
            var todos = [].concat.apply([], partes);
            todos.sort(function (x, y) {
                var ox = ORDEN[x.nivel] === undefined ? 2 : ORDEN[x.nivel];
                var oy = ORDEN[y.nivel] === undefined ? 2 : ORDEN[y.nivel];
                return ox - oy;
            });
            actuales = todos;
            pintar();
        });
    }

    // Al abrir la lista: todos los actuales pasan a vistos (el punto se apaga).
    btn.addEventListener('click', function () {
        var ids = vistos();
        actuales.forEach(function (a) { if (ids.indexOf(String(a.id)) === -1) { ids.push(String(a.id)); } });
        guardarVistos(ids);
        actualizarPunto();
    });

    document.addEventListener('visibilitychange', function () { if (!document.hidden) { cargar(); } });
    setInterval(function () { if (!document.hidden) { cargar(); } }, REFRESCO);
    window.HMCampana = { recargar: cargar };
    cargar();
})();
