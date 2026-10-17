/* =========================================================================
   hm-motion.js — helpers de movimiento sutil del ecosistema SSO (sin jQuery)
   Original en SSO/hm-motion; se copia a cada app junto a hm-motion.css.

   HMMotion.reducido                    true si el sistema pide menos movimiento
   HMMotion.contar(el, valor, fmt, desde)
                                        número que cuenta hasta su valor (450 ms, ease-out).
                                        `desde` = valor anterior (para refrescos). Sin cambio o con
                                        movimiento reducido o pestaña oculta: valor final directo.
   HMMotion.entrar(contenedor, sel)     cascada (P1): pone hm-m-entra y --i a los hijos que coinciden
   HMMotion.barra(el, pct)              medidor que crece hasta pct (P4)
   HMMotion.fade(el)                    fundido corto (pestañas)
   HMMotion.ocupar(zona, on, texto)     R5: bloquea la zona del reporte mientras carga (clics, botones,
                                        inputs, selects) con aria-busy, sin oscurecer la pantalla. El
                                        indicador (uno solo, en la primera zona) aparece si tarda más de 200 ms.
   HMMotion.apex()                      bloque `animations` para ApexCharts (primer dibujado)
   HMMotion.apexSinAnimar()             para updateOptions o redibujos: sin animación
   HMMotion.chartjs()                   bloque `animation` para Chart.js v2 (Onuris)
   ========================================================================= */
(function (w, d) {
    'use strict';
    if (w.HMMotion) { return; }
    var mq = w.matchMedia ? w.matchMedia('(prefers-reduced-motion: reduce)') : null;
    var M = { reducido: !!(mq && mq.matches) };
    if (mq && mq.addEventListener) { mq.addEventListener('change', function (e) { M.reducido = e.matches; }); }

    function num(v) {
        if (typeof v === 'number') { return v; }
        var n = parseFloat(String(v === null || v === undefined ? '' : v).replace(/\s/g, '').replace(/\.(?=\d{3}(\D|$))/g, '').replace(',', '.'));
        return isFinite(n) ? n : NaN;
    }
    function fmtDef(n) { return Math.round(n).toLocaleString('es-EC'); }

    M.contar = function (el, valor, fmt, desde) {
        if (!el) { return; }
        fmt = fmt || fmtDef;
        var fin = num(valor), ini = desde === undefined || desde === null ? 0 : num(desde);
        if (!isFinite(ini)) { ini = 0; }
        if (!isFinite(fin)) { el.textContent = valor === null || valor === undefined ? '' : String(valor); return; }
        if (M.reducido || fin === ini || d.hidden) { el.textContent = fmt(fin); return; }
        var t0 = null, dur = 450, tok = {};
        el.__hmContar = tok;                       // una nueva cuenta cancela la anterior
        function paso(t) {
            if (el.__hmContar !== tok) { return; }
            if (t0 === null) { t0 = t; }
            var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(ini + (fin - ini) * e);
            if (p < 1) { w.requestAnimationFrame(paso); } else { el.textContent = fmt(fin); }
        }
        w.requestAnimationFrame(paso);
    };

    M.entrar = function (contenedor, sel) {
        if (!contenedor) { return; }
        var hijos = sel ? contenedor.querySelectorAll(sel) : contenedor.children;
        Array.prototype.forEach.call(hijos, function (h, i) {
            h.style.setProperty('--i', i);
            h.classList.remove('hm-m-entra'); void h.offsetWidth; h.classList.add('hm-m-entra');
        });
    };

    M.barra = function (el, pct) {
        if (!el) { return; }
        el.classList.add('hm-m-barra');
        var v = Math.max(0, Math.min(100, +pct || 0)) + '%';
        if (M.reducido) { el.style.width = v; return; }
        w.requestAnimationFrame(function () { w.requestAnimationFrame(function () { el.style.width = v; }); });
    };

    M.fade = function (el) {
        if (!el || M.reducido) { return; }
        el.classList.remove('hm-m-fade'); void el.offsetWidth; el.classList.add('hm-m-fade');
    };

    var CTRL = 'button, input, select, textarea, a.btn, .btn';
    M.ocupar = function (zona, on, texto) {
        var zonas = !zona ? [] : (zona.length !== undefined && !zona.nodeType ? Array.prototype.slice.call(zona) : [zona]);
        var conInd = null;                                 // un solo indicador: en la primera zona válida
        zonas.forEach(function (z) {
            if (!z) { return; }
            if (on && conInd === null) { conInd = z; }
            if (on) {
                if (z.__hmOcupado) { return; }
                z.__hmOcupado = true;
                z.classList.add('hm-m-ocupado'); z.setAttribute('aria-busy', 'true');
                z.__hmDes = [];
                Array.prototype.forEach.call(z.querySelectorAll(CTRL), function (c) {
                    if (c.disabled || c.getAttribute('aria-disabled') === 'true') { return; }
                    z.__hmDes.push(c);
                    if ('disabled' in c && c.tagName !== 'A') { c.disabled = true; } else { c.setAttribute('aria-disabled', 'true'); c.setAttribute('tabindex', '-1'); }
                });
                if (z !== conInd) { return; }
                z.__hmT = w.setTimeout(function () {
                    if (!z.__hmOcupado || z.querySelector(':scope > .hm-m-ocupado-ind')) { return; }
                    var ind = d.createElement('div'); ind.className = 'hm-m-ocupado-ind'; ind.setAttribute('role', 'status');
                    ind.innerHTML = '<span class="hm-m-gira" aria-hidden="true">&#x27F3;</span>';
                    ind.appendChild(d.createTextNode(texto || 'Cargando…'));
                    z.appendChild(ind);
                }, 200);
            } else {
                if (!z.__hmOcupado) { return; }
                z.__hmOcupado = false; w.clearTimeout(z.__hmT);
                z.classList.remove('hm-m-ocupado'); z.removeAttribute('aria-busy');
                (z.__hmDes || []).forEach(function (c) {
                    if ('disabled' in c && c.tagName !== 'A') { c.disabled = false; } else { c.removeAttribute('aria-disabled'); c.removeAttribute('tabindex'); }
                });
                z.__hmDes = [];
                var ind = z.querySelector(':scope > .hm-m-ocupado-ind'); if (ind) { ind.remove(); }
            }
        });
    };

    M.apex = function () {
        return { enabled: !M.reducido, easing: 'easeout', speed: 420, animateGradually: { enabled: true, delay: 40 }, dynamicAnimation: { enabled: false } };
    };
    M.apexSinAnimar = function () { return { enabled: false }; };
    M.chartjs = function () { return { duration: M.reducido ? 0 : 420, easing: 'easeOutCubic' }; };

    w.HMMotion = M;
})(window, document);
