{{--
  L3 / R-03: tema sin destello. Va al COMIENZO del <head>, antes de cualquier hoja.
  - Cookie hm_tema = claro|oscuro (la escribe hm-tema.js en .24hm.net): el servidor ya emite las hojas
    correctas (partials/hm-tema-link) y no hace falta nada aqui.
  - auto o sin cookie: este script decide con prefers-color-scheme (o la clave vieja de localStorage)
    ANTES de que el parser llegue a las hojas; hm-tema-link las escribe con el href ya elegido, asi
    son hojas del parser (bloquean el render) y la primera pintura sale con el tema correcto.
  Parametro: $clave = clave de localStorage de la app (la misma que data-clave de hm-tema.js).
--}}
@php $hmTemaPref = request()->cookie('hm_tema'); @endphp
@unless (in_array($hmTemaPref, ['claro', 'oscuro'], true))
<script>
    window.HMTemaOscuro = (function () {
        var m = document.cookie.match(/(?:^|;\s*)hm_tema=(auto|claro|oscuro)(?:;|$)/), t = m ? m[1] : null;
        if (!t) { try { t = localStorage.getItem(@json($clave)); } catch (e) { t = null; } }
        var o = t === 'oscuro' || (t !== 'claro' && !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));
        document.documentElement.setAttribute('data-tema', o ? 'oscuro' : 'claro');
        return o;
    })();
</script>
@endunless
