{{--
  L3 / R-03: una hoja con variante clara/oscura (bootstrap o app de Nazox) sin destello.
  Con cookie hm_tema = claro|oscuro el servidor emite el href correcto; con auto o sin cookie lo escribe
  el parser con lo que decidio partials/hm-tema-ini. hm-tema.js sigue manejando el cambio en vivo
  (boton, cambio del sistema) con data-claro / data-oscuro y solo toca el href si no coincide.
  Parametros: $id, $claro, $oscuro (URLs ya resueltas).
--}}
@php $hmTemaPref = request()->cookie('hm_tema'); @endphp
@if (in_array($hmTemaPref, ['claro', 'oscuro'], true))
<link href="{{ $hmTemaPref === 'oscuro' ? $oscuro : $claro }}" id="{{ $id }}" data-claro="{{ $claro }}" data-oscuro="{{ $oscuro }}" rel="stylesheet" type="text/css" />
@else
<script>
    (function (c, o) {
        document.write('<link href="' + (window.HMTemaOscuro ? o : c) + '" id="' + @json($id) + '" data-claro="' + c + '" data-oscuro="' + o + '" rel="stylesheet" type="text/css" />');
    })(@json($claro), @json($oscuro));
</script>
<noscript><link href="{{ $claro }}" id="{{ $id }}" rel="stylesheet" type="text/css" /></noscript>
@endif
