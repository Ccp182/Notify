@extends('layouts.app')

@section('title', '| Dashboard')

@push('css')
<link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css">
<style>
    /* Select2 alineado con BS5 (alto ~31px del form-control-sm) */
    .select2-container--default .select2-selection--multiple {
        min-height: 31px; border-color: #ced4da; border-radius: .25rem; font-size: .8125rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e7f1ff; border-color: #bbd6fe; color: #0d6efd;
    }
</style>
<style>
    /* Tarjetas de KPI con bordes suaves y color en el numero */
    .kpi-card       { border: 1px solid #e9ecef; border-radius: .5rem; background: #fff; height: 100%; }
    .kpi-card .body { padding: 1rem; }
    .kpi-label      { font-size: .75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
    .kpi-value      { font-size: 1.75rem; font-weight: 600; line-height: 1.1; margin: .25rem 0 0; }
    .kpi-suffix     { font-size: .85rem; font-weight: 500; color: #6c757d; }
    .kpi-hint       { font-size: .7rem; color: #adb5bd; margin-top: .25rem; }
    .kpi-rate-ok    { color: #198754; }
    .kpi-rate-warn  { color: #f0ad4e; }
    .kpi-rate-bad   { color: #dc3545; }

    /* Listas compactas */
    .mini-list        { list-style: none; padding: 0; margin: 0; }
    .mini-list li     { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid #f1f3f5; }
    .mini-list li:last-child { border-bottom: 0; }
    .mini-list .name  { flex-grow: 1; min-width: 0; }
    .mini-list .name .n1 { font-size: .9rem; font-weight: 500; color: #212529; }
    .mini-list .name .n2 { font-size: .75rem; color: #6c757d; }
    .mini-list .aside { font-size: .8rem; color: #6c757d; white-space: nowrap; }
</style>
@endpush

@section('content')

    {{-- ============================ Filtros de rango ============================ --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                        <span class="text-muted small me-2"><i class="ri-calendar-line me-1"></i> Período:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-rango" data-dias="7">7 días</button>
                        <button type="button" class="btn btn-sm btn-primary      js-rango" data-dias="30">30 días</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-rango" data-dias="90">90 días</button>
                        <div class="vr mx-2"></div>
                        <input type="date" id="dbFechaInicio" class="form-control form-control-sm" style="max-width:160px;">
                        <span class="text-muted small">-</span>
                        <input type="date" id="dbFechaFin"    class="form-control form-control-sm" style="max-width:160px;">
                        <button type="button" id="btnDbAplicar" class="btn btn-sm btn-dark">
                            <i class="ri-refresh-line"></i> Aplicar
                        </button>
                        <small class="text-muted ms-auto" id="dbPeriodoLbl">-</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap mt-2" style="gap:.5rem;">
                        <span class="text-muted small me-2"><i class="ri-bullseye-line me-1"></i> Campañas:</span>
                        <select id="dbIdCampaigns" multiple class="form-select form-select-sm" style="min-width:320px; max-width:600px;"></select>
                        <button type="button" id="btnDbLimpiarCampanas" class="btn btn-sm btn-light" title="Quitar filtro de campañas">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================ Fila 1: KPIs ============================ --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Campañas</div>
                <div class="kpi-value text-dark" id="kpi-total">0</div>
                <div class="kpi-hint"><span id="kpi-ejecucion">0</span> en ejecución</div>
            </div></div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Audiencia alcanzada</div>
                <div class="kpi-value text-dark" id="kpi-audiencia">0</div>
                <div class="kpi-hint">dispositivos únicos</div>
            </div></div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Tasa de entrega</div>
                <div class="kpi-value" id="kpi-tasa-entrega">0<span class="kpi-suffix">%</span></div>
                <div class="kpi-hint"><span id="kpi-entregadas">0</span> entregadas</div>
            </div></div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Tasa de confirmación</div>
                <div class="kpi-value" id="kpi-tasa-conf">0<span class="kpi-suffix">%</span></div>
                <div class="kpi-hint"><span id="kpi-confirmadas">0</span> confirmadas</div>
            </div></div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Tasa de fallo</div>
                <div class="kpi-value" id="kpi-tasa-fallo">0<span class="kpi-suffix">%</span></div>
                <div class="kpi-hint"><span id="kpi-fallidas">0</span> fallidas</div>
            </div></div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="kpi-card"><div class="body">
                <div class="kpi-label">Programadas</div>
                <div class="kpi-value text-warning" id="kpi-programadas">0</div>
                <div class="kpi-hint"><span id="kpi-completadas">0</span> completadas</div>
            </div></div>
        </div>
    </div>

    {{-- ============================ Fila 2: Graficos principales ============================ --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">Evolución de alcance</h5>
                        <small class="text-muted">Audiencia, entregas y confirmaciones por día</small>
                    </div>
                    <div id="chartSerie" style="min-height:300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">Distribución del período</h5>
                    <div id="chartDonut" style="min-height:300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================ Fila 3: Ranking y acceso rapido ============================ --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">Top campañas por alcance</h5>
                        <a href="{{ route('campains.index') }}" class="small">Ver todas &rarr;</a>
                    </div>
                    <div id="chartTop" style="min-height:260px;"></div>
                    <ul class="mini-list mt-2" id="topList"><li class="text-muted small">Cargando...</li></ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1">Acciones rápidas</h5>
                    <p class="text-muted small mb-3">Crea una campaña o revisa las que tienes.</p>
                    <a href="{{ route('wizard') }}" class="btn btn-primary mb-2">
                        <i class="mdi mdi-bullhorn-outline me-1"></i> Nueva campaña
                    </a>
                    <a href="{{ route('campains.index') }}" class="btn btn-outline-secondary mb-3">
                        <i class="ri-list-check-2 me-1"></i> Ver todas las campañas
                    </a>
                    <div class="mt-auto small text-muted">
                        <div><i class="ri-apps-2-line me-1"></i> App: <strong>{{ session('AppNotify.name', '-') }}</strong></div>
                        <div><i class="ri-earth-line  me-1"></i> País: <strong>{{ session('Pais', 'EC') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/libs/select2/js/i18n/es.js') }}"></script>
<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });

    // ---------- Estado / helpers ----------
    let state = {
        fechaInicio: null,
        fechaFin:    null,
        idCampaigns: [],
    };

    function toISO(d) { return d.toISOString().slice(0, 10); }

    function rangoDias(n) {
        const fin    = new Date();
        const inicio = new Date(); inicio.setDate(inicio.getDate() - (n - 1));
        return { fechaInicio: toISO(inicio), fechaFin: toISO(fin) };
    }

    function fmtInt(n) {
        n = parseInt(n || 0, 10);
        return n.toLocaleString('es-EC');
    }

    function colorTasa(pct) {
        if (pct >= 80) return 'kpi-rate-ok';
        if (pct >= 40) return 'kpi-rate-warn';
        return 'kpi-rate-bad';
    }

    function paintTasa($el, pct) {
        $el.removeClass('kpi-rate-ok kpi-rate-warn kpi-rate-bad').addClass(colorTasa(pct));
        $el.html(Number(pct || 0).toFixed(1) + '<span class="kpi-suffix">%</span>');
    }

    // ---------- Carga de datos ----------
    function cargarDashboard() {
        const payload = {
            fechaInicio: state.fechaInicio,
            fechaFin:    state.fechaFin,
            idCampaigns: state.idCampaigns,
        };
        const sufijo = state.idCampaigns.length ? ' - ' + state.idCampaigns.length + ' campaña(s)' : '';
        $('#dbPeriodoLbl').text('Período: ' + state.fechaInicio + ' a ' + state.fechaFin + sufijo);

        $.post('{{ route('dashboard.resumen') }}', payload).done(function (resp) {
            const r = resp.resumen || {};
            $('#kpi-total').text(fmtInt(r.TotalCampanas));
            $('#kpi-ejecucion').text(fmtInt(r.EnEjecucion));
            $('#kpi-audiencia').text(fmtInt(r.AudienciaReal));
            $('#kpi-entregadas').text(fmtInt(r.Entregadas));
            $('#kpi-confirmadas').text(fmtInt(r.Confirmadas));
            $('#kpi-fallidas').text(fmtInt(r.Fallidas));
            $('#kpi-programadas').text(fmtInt(r.Programadas));
            $('#kpi-completadas').text(fmtInt(r.Completadas));
            paintTasa($('#kpi-tasa-entrega'), r.TasaEntrega);
            paintTasa($('#kpi-tasa-conf'),    r.TasaConfirmacion);
            paintTasa($('#kpi-tasa-fallo'),   100 - (r.TasaFallo || 0));
            // La card de "fallo" invertimos el color (mas fallo = peor); hack: restamos de 100
            $('#kpi-tasa-fallo').html(Number(r.TasaFallo || 0).toFixed(1) + '<span class="kpi-suffix">%</span>');
            // Reaplicar color de "fallo" en sentido correcto: 0% fallo = verde, alto = rojo
            $('#kpi-tasa-fallo').removeClass('kpi-rate-ok kpi-rate-warn kpi-rate-bad');
            const pf = r.TasaFallo || 0;
            $('#kpi-tasa-fallo').addClass(pf < 10 ? 'kpi-rate-ok' : (pf < 30 ? 'kpi-rate-warn' : 'kpi-rate-bad'));

            renderDonut(r);
        });

        $.post('{{ route('dashboard.serie') }}', payload).done(function (resp) {
            renderSerie(resp.serie || []);
        });

        $.post('{{ route('dashboard.top') }}', $.extend({}, payload, { top: 5 })).done(function (resp) {
            renderTop(resp.top || []);
        });
    }

    // ---------- Graficos ----------
    let chSerie = null, chDonut = null, chTop = null;

    function renderSerie(rows) {
        const cats = rows.map(r => r.Fecha);
        const audiencia   = rows.map(r => +r.AudienciaReal || 0);
        const entregadas  = rows.map(r => +r.Entregadas    || 0);
        const confirmadas = rows.map(r => +r.Confirmadas   || 0);

        const opts = {
            chart: { type: 'line', height: 300, toolbar: { show: false }, animations: { enabled: false } },
            stroke: { curve: 'smooth', width: [0, 3, 3] },
            colors: ['#adb5bd', '#0d6efd', '#198754'],
            series: [
                { name: 'Audiencia',   type: 'area',   data: audiencia },
                { name: 'Entregadas',  type: 'line',   data: entregadas },
                { name: 'Confirmadas', type: 'line',   data: confirmadas }
            ],
            fill: { opacity: [0.12, 1, 1] },
            xaxis: { categories: cats, labels: { rotate: -45, style: { fontSize: '10px' } } },
            yaxis: { labels: { formatter: v => fmtInt(v) } },
            legend: { position: 'top' },
            tooltip: { shared: true, y: { formatter: v => fmtInt(v) + ' dispositivos' } },
            grid: { borderColor: '#f1f3f5' }
        };

        if (chSerie) { chSerie.updateOptions(opts); }
        else         { chSerie = new ApexCharts(document.querySelector('#chartSerie'), opts); chSerie.render(); }
    }

    function renderDonut(r) {
        const series = [
            +r.Confirmadas || 0,
            +r.Enviadas    || 0,
            +r.Pendientes  || 0,
            +r.Fallidas    || 0
        ];
        const totalSum = series.reduce((a, b) => a + b, 0);

        const opts = {
            chart:  { type: 'donut', height: 300 },
            labels: ['Confirmadas', 'Enviadas', 'Pendientes', 'Fallidas'],
            colors: ['#198754', '#0dcaf0', '#6c757d', '#dc3545'],
            series: totalSum === 0 ? [1] : series,
            legend: { position: 'bottom' },
            dataLabels: { enabled: totalSum !== 0 },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: { show: true, label: 'Audiencia', formatter: () => fmtInt(totalSum) }
                        }
                    }
                }
            },
            noData: { text: 'Sin datos en el período' }
        };

        if (totalSum === 0) {
            opts.labels = ['Sin datos'];
            opts.colors = ['#e9ecef'];
            opts.dataLabels = { enabled: false };
            opts.tooltip = { enabled: false };
        }

        if (chDonut) { chDonut.destroy(); }
        chDonut = new ApexCharts(document.querySelector('#chartDonut'), opts);
        chDonut.render();
    }

    function renderTop(rows) {
        const $list = $('#topList').empty();

        if (!rows.length) {
            $('#chartTop').empty().html('<div class="text-center text-muted py-5">No hay campañas con entregas en el período</div>');
            $list.append('<li class="text-muted small">Sin datos</li>');
            if (chTop) { chTop.destroy(); chTop = null; }
            return;
        }

        const cats = rows.map(r => r.Name);
        const ent  = rows.map(r => +r.Entregadas    || 0);
        const aud  = rows.map(r => +r.AudienciaReal || 0);

        const opts = {
            chart:  { type: 'bar', height: 260, toolbar: { show: false }, animations: { enabled: false } },
            plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3 } },
            colors: ['#0d6efd', '#adb5bd'],
            dataLabels: { enabled: true, formatter: v => fmtInt(v) },
            series: [
                { name: 'Entregadas', data: ent },
                { name: 'Audiencia',  data: aud }
            ],
            xaxis: { categories: cats, labels: { formatter: v => fmtInt(v) } },
            legend: { position: 'top' },
            grid: { borderColor: '#f1f3f5' }
        };

        if (chTop) { chTop.destroy(); }
        chTop = new ApexCharts(document.querySelector('#chartTop'), opts);
        chTop.render();

        rows.forEach(function (r) {
            const pct = Number(r.PctEntrega || 0).toFixed(0);
            $list.append(
                '<li>' +
                  '<div class="name">' +
                    '<div class="n1">' + $('<div>').text(r.Name || '').html() + '</div>' +
                    '<div class="n2">' + fmtInt(r.Entregadas) + ' de ' + fmtInt(r.AudienciaReal) + ' dispositivos</div>' +
                  '</div>' +
                  '<div class="aside"><strong>' + pct + '%</strong></div>' +
                '</li>'
            );
        });
    }

    // ---------- Eventos ----------
    $('.js-rango').on('click', function () {
        const n = parseInt($(this).data('dias'), 10);
        const r = rangoDias(n);
        state.fechaInicio = r.fechaInicio;
        state.fechaFin    = r.fechaFin;
        $('#dbFechaInicio').val(r.fechaInicio);
        $('#dbFechaFin').val(r.fechaFin);
        $('.js-rango').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).addClass('btn-primary').removeClass('btn-outline-secondary');
        cargarDashboard();
    });

    $('#btnDbAplicar').on('click', function () {
        const fi = $('#dbFechaInicio').val();
        const ff = $('#dbFechaFin').val();
        if (!fi || !ff) { return; }
        if (fi > ff)    { return; }
        state.fechaInicio = fi;
        state.fechaFin    = ff;
        $('.js-rango').removeClass('btn-primary').addClass('btn-outline-secondary');
        cargarDashboard();
    });

    // ---------- Filtro multi-campana (Select2) ----------
    // Cargamos las campanas del usuario una sola vez al abrir el dashboard.
    // Si crea campanas nuevas, recargar la pagina basta (no pesa).
    function initSelectCampanas() {
        const $sel = $('#dbIdCampaigns');
        $sel.select2({
            language: 'es',
            placeholder: 'Todas las campañas',
            allowClear: true,
            closeOnSelect: false,
            width: 'resolve'
        });

        $.post('{{ route('campains.list') }}').done(function (resp) {
            const rows = resp.data || [];
            rows.forEach(function (r) {
                if (!r.IdCampaign) return;
                const opt = new Option(r.Name || ('#' + r.IdCampaign), r.IdCampaign, false, false);
                $sel.append(opt);
            });
            $sel.trigger('change');
        });

        $sel.on('change', function () {
            const vals = $(this).val() || [];
            state.idCampaigns = vals.map(v => parseInt(v, 10)).filter(v => v > 0);
            cargarDashboard();
        });
    }

    $('#btnDbLimpiarCampanas').on('click', function () {
        $('#dbIdCampaigns').val(null).trigger('change');
    });

    // ---------- Inicializacion: 30 dias ----------
    const initial = rangoDias(30);
    state.fechaInicio = initial.fechaInicio;
    state.fechaFin    = initial.fechaFin;
    $('#dbFechaInicio').val(initial.fechaInicio);
    $('#dbFechaFin').val(initial.fechaFin);
    initSelectCampanas();
    cargarDashboard();
});
</script>
@endpush
