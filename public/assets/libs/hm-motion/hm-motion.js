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
   HMMotion.ocupar(zona, on, texto)     R5: bloquea las zonas con inert (sin clics ni teclado, como el Swal),
                                        con velo y aria-busy, y muestra UNA tarjeta centrada (spinner + texto).
                                        texto: string (título) u {titulo, sub}. Aparece si tarda más de 200 ms.
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

    /* R5 v3 (pedido de Carlos, 17-oct): bloqueo REAL como el Swal (inert: sin clics ni teclado) en las zonas,
       con velo atenuado, y UN solo indicador: la tarjeta centrada del mapa de Anubis (spinner + título +
       subtítulo), centrada sobre las zonas bloqueadas. Mientras dura se oculta el "Procesando…" de DataTables.
       Bloquear y liberar son idempotentes por zona; la tarjeta vive mientras quede alguna zona ocupada. */
    var ocupadas = [], tarjeta = null, tTarjeta = null;
    function rectUnion() {
        var r = null;
        ocupadas.forEach(function (z) {
            var b = z.getBoundingClientRect(); if (!b.width && !b.height) { return; }
            r = r ? { l: Math.min(r.l, b.left), t: Math.min(r.t, b.top), rr: Math.max(r.rr, b.right), b: Math.max(r.b, b.bottom) } : { l: b.left, t: b.top, rr: b.right, b: b.bottom };
        });
        return r;
    }
    function ubicarTarjeta() {
        if (!tarjeta) { return; }
        var u = rectUnion(), vw = w.innerWidth, vh = w.innerHeight;
        if (!u) { tarjeta.style.left = '50%'; tarjeta.style.top = '50%'; return; }
        var l = Math.max(0, u.l), r = Math.min(vw, u.rr), t = Math.max(0, u.t), b = Math.min(vh, u.b);
        tarjeta.style.left = (r > l ? (l + r) / 2 : vw / 2) + 'px';
        tarjeta.style.top = (b > t ? (t + b) / 2 : vh / 2) + 'px';
    }
    function mostrarTarjeta(texto) {
        var tit = 'Cargando…', sub = 'Consultando los datos';
        if (typeof texto === 'string' && texto) { tit = texto; }
        else if (texto && typeof texto === 'object') { tit = texto.titulo || tit; sub = texto.sub === undefined ? sub : texto.sub; }
        if (!tarjeta) {
            tarjeta = d.createElement('div'); tarjeta.className = 'hm-m-cargando'; tarjeta.setAttribute('role', 'status'); tarjeta.setAttribute('aria-live', 'polite');
            tarjeta.innerHTML = '<span class="hm-m-cargando-giro" aria-hidden="true"></span><span class="hm-m-cargando-txt"><strong></strong><small></small></span>';
            d.body.appendChild(tarjeta);
            w.addEventListener('scroll', ubicarTarjeta, true); w.addEventListener('resize', ubicarTarjeta);
        }
        tarjeta.querySelector('strong').textContent = tit;
        var s = tarjeta.querySelector('small'); s.textContent = sub || ''; s.hidden = !sub;
        ubicarTarjeta();
    }
    function quitarTarjeta() {
        w.clearTimeout(tTarjeta); tTarjeta = null;
        if (tarjeta) { tarjeta.remove(); tarjeta = null; w.removeEventListener('scroll', ubicarTarjeta, true); w.removeEventListener('resize', ubicarTarjeta); }
    }
    M.ocupar = function (zona, on, texto) {
        var zonas = !zona ? [] : (zona.length !== undefined && !zona.nodeType ? Array.prototype.slice.call(zona) : [zona]);
        zonas.forEach(function (z) {
            if (!z || !z.nodeType) { return; }
            if (on) {
                if (z.__hmN) { return; }          // idempotente: bloquear dos veces = una (igual que antes)
                z.__hmN = 1;
                z.__hmInert = z.hasAttribute('inert');
                // si el foco estaba dentro, sale de la zona (inert lo dejaría en un elemento inalcanzable)
                if (d.activeElement && z.contains(d.activeElement) && d.activeElement.blur) { d.activeElement.blur(); }
                z.setAttribute('inert', '');
                z.classList.add('hm-m-ocupado'); z.setAttribute('aria-busy', 'true');
                ocupadas.push(z);
            } else {
                if (!z.__hmN) { return; }
                z.__hmN = 0;
                if (!z.__hmInert) { z.removeAttribute('inert'); }
                z.classList.remove('hm-m-ocupado'); z.removeAttribute('aria-busy');
                ocupadas = ocupadas.filter(function (x) { return x !== z; });
            }
        });
        if (ocupadas.length) {
            d.documentElement.classList.add('hm-m-ocupando');
            if (tarjeta) { mostrarTarjeta(texto); }
            else if (!tTarjeta) { tTarjeta = w.setTimeout(function () { tTarjeta = null; if (ocupadas.length) { mostrarTarjeta(texto); } }, 200); }
        } else {
            d.documentElement.classList.remove('hm-m-ocupando');
            quitarTarjeta();
        }
    };

    M.apex = function () {
        return { enabled: !M.reducido, easing: 'easeout', speed: 420, animateGradually: { enabled: true, delay: 40 }, dynamicAnimation: { enabled: false } };
    };
    M.apexSinAnimar = function () { return { enabled: false }; };
    M.chartjs = function () { return { duration: M.reducido ? 0 : 420, easing: 'easeOutCubic' }; };

    w.HMMotion = M;
})(window, document);
