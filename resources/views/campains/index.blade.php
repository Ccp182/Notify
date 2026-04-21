@extends('layouts.app')

@section('title', '| Campañas')

@push('css')
    <link href="{{ asset('assets/libs/datatables.net-libs/DataTables-2.0.0/css/dataTables.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Ajustes propios del listado de campanas (heredan custom.css) */
        #tblCampanas_wrapper { font-size: 12px; }
        #tblCampanas { width: 100% !important; }
        #tblCampanas_wrapper tr { cursor: default; }
    </style>
@endpush

@section('content')

    {{-- ================= Filtros ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" for="filtroFechaInicio">Desde</label>
                            <input type="date" class="form-control" id="filtroFechaInicio">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="filtroFechaFin">Hasta</label>
                            <input type="date" class="form-control" id="filtroFechaFin">
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="button" class="btn btn-primary" id="btnAplicarFiltro">
                                <i class="ri-filter-3-line align-middle me-1"></i> Aplicar
                            </button>
                            <button type="button" class="btn btn-light" id="btnLimpiarFiltro">
                                <i class="ri-close-line align-middle me-1"></i> Limpiar
                            </button>
                            <a href="{{ route('wizard') }}" class="btn btn-success ms-1">
                                <i class="mdi mdi-bullhorn-outline align-middle me-1"></i> Campaña nueva
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Tabla ================= --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Mis campañas</h4>
                        <small class="text-muted" id="lblTotalCampanas">&nbsp;</small>
                    </div>

                    <div class="table-responsive">
                        <table id="tblCampanas" class="table table-centered datatable dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead class="table-light">
                                <tr>
                                    <th>Campaña</th>
                                    <th>Tipo envío</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                    <th>Segmentación</th>
                                    <th>Entrega</th>
                                    <th>Última actualización</th>
                                    <th>Creado por</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- ================= Modal Drill-down ================= --}}
                    <div class="modal fade" id="modalEntrega" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Detalle de entrega
                                        <small class="text-muted ms-2" id="modalEntregaCampana"></small>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- Resumen --}}
                                    {{--
                                        Paleta (BS5):
                                          - Audiencia real: text-dark     (total, neutral)
                                          - Pendientes:     text-secondary (en espera, sin juicio)
                                          - Enviadas:       text-info      (celeste, en transito)
                                          - Confirmadas:    text-success   (verde, happy path completo)
                                          - Fallidas:       text-danger    (rojo, error)
                                          - Entregadas:     azul fijo #0d6efd (NO usar text-primary: en apps
                                                            HM el primary esta pisado por el color institucional
                                                            - p.ej. HMMovil=rojo - y se confundiria con Fallidas)
                                    --}}
                                    <div class="row g-2 mb-3" id="modalEntregaResumen">
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Audiencia real
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Total de dispositivos a los que se intentó llegar con esta campaña. Es la base sobre la que se calculan todos los demás indicadores."></i>
                                                </div>
                                                <div class="h5 mb-0 text-dark" id="met-AudienciaReal">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Pendientes
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Dispositivos que están en cola y aún no han sido procesados para el envío. Normalmente se reduce a cero en pocos segundos."></i>
                                                </div>
                                                <div class="h5 mb-0 text-secondary" id="met-Pendientes">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Enviadas
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Notificaciones que ya salieron hacia el dispositivo pero aún no hay confirmación de que hayan sido recibidas."></i>
                                                </div>
                                                <div class="h5 mb-0 text-info" id="met-Enviadas">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Confirmadas
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Notificaciones entregadas con confirmación de recibido por parte del dispositivo."></i>
                                                </div>
                                                <div class="h5 mb-0 text-success" id="met-Confirmadas">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Fallidas
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Dispositivos a los que no se pudo entregar la notificación. Causas típicas: la app fue desinstalada, el dispositivo está inactivo, o el usuario desactivó las notificaciones."></i>
                                                </div>
                                                <div class="h5 mb-0 text-danger" id="met-Fallidas">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="p-2 text-center border rounded h-100">
                                                <div class="small text-muted">
                                                    Entregadas
                                                    <i class="ri-information-line ms-1 text-muted" role="button"
                                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                       title="Total de notificaciones que llegaron al dispositivo (con o sin confirmación). Es el indicador principal de alcance de la campaña y el que se muestra en la barra de Entrega del listado."></i>
                                                </div>
                                                <div class="h5 mb-0 fw-semibold" style="color:#0d6efd;" id="met-Entregadas">0</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Filtro por estado --}}
                                    <div class="d-flex align-items-center mb-2">
                                        <label class="me-2 mb-0 small text-muted">Filtrar por estado:</label>
                                        <select id="filtroEstadoPush" class="form-select form-select-sm w-auto">
                                            <option value="">Todos</option>
                                            <option value="APP">Pendiente (APP)</option>
                                            <option value="SEN">Enviado (SEN)</option>
                                            <option value="FIN">Confirmado (FIN)</option>
                                            <option value="N">Fallido (N)</option>
                                        </select>
                                    </div>

                                    {{-- Tabla detalle --}}
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Min</th>
                                                    <th>DId</th>
                                                    <th>App</th>
                                                    <th>Secuencia alerta</th>
                                                    <th>Estado push</th>
                                                    <th>Fecha push</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tblDetalleEntregaBody">
                                                <tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Paginacion --}}
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <small class="text-muted" id="lblDetallePaginacion">&nbsp;</small>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary" id="btnDetallePrev">&laquo; Anterior</button>
                                            <button class="btn btn-sm btn-outline-secondary" id="btnDetalleNext">Siguiente &raquo;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net-libs/DataTables-2.0.0/js/dataTables.min.js') }}"></script>
<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });

    // ----- Helpers de formato -----
    function fmtFecha(s) {
        if (!s) return '-';
        // s viene "YYYY-MM-DD HH:mm:ss" del backend
        return s.replace('T', ' ').substring(0, 16);
    }

    function badgeEstado(estado) {
        switch (estado) {
            case 'EnEjecucion':
                return '<span class="badge bg-success">En ejecución</span>';
            case 'Programada':
                return '<span class="badge bg-warning text-dark">Programada</span>';
            case 'Completada':
                return '<span class="badge bg-secondary">Completada</span>';
            default:
                return '<span class="badge bg-light text-dark">' + (estado || '-') + '</span>';
        }
    }

    function badgeTipoEnvio(schedType) {
        switch (schedType) {
            case 'NOW':
                return '<span class="badge bg-soft-secondary text-dark"><i class="mdi mdi-flash"></i> Inmediato</span>';
            case 'ONCE':
                return '<span class="badge bg-soft-warning text-warning"><i class="mdi mdi-calendar-clock"></i> Programado</span>';
            case 'DAILY':
                return '<span class="badge bg-soft-info text-info"><i class="mdi mdi-repeat"></i> Diario</span>';
            case 'CUSTOM':
                return '<span class="badge bg-soft-primary text-primary"><i class="mdi mdi-calendar-multiselect"></i> Personalizado</span>';
            default:
                return '<span class="badge bg-light text-dark">' + (schedType || '-') + '</span>';
        }
    }

    function badgeSegmentacion(seg) {
        if (seg === 'ANDROID') return '<span class="badge bg-soft-success text-success"><i class="mdi mdi-android"></i> Android</span>';
        if (seg === 'IOS')     return '<span class="badge bg-soft-secondary text-dark"><i class="mdi mdi-apple"></i> iOS</span>';
        if (seg === 'TODAS')   return '<span class="badge bg-soft-primary text-primary"><i class="mdi mdi-cellphone"></i> Todas</span>';
        return '<span class="badge bg-light text-dark">' + (seg || '-') + '</span>';
    }

    function nombreCol(row) {
        var name = $('<div>').text(row.Name || '-').html();
        var desc = row.Description ? $('<div>').text(row.Description).html() : '';
        return '<div><strong>' + name + '</strong></div>' +
               (desc ? '<div class="text-muted small">' + desc + '</div>' : '');
    }

    // Barra de entrega: Entregadas / TotalDispositivos. Si total=0 (programada
    // sin disparar todavia), mostramos un guion.
    function entregaCol(row) {
        var total = parseInt(row.TotalDispositivos || 0, 10);
        var ok    = parseInt(row.Entregadas        || 0, 10);
        if (total <= 0) {
            return '<span class="text-muted small">Sin datos</span>';
        }
        var pct = Math.round((ok / total) * 100);
        var clase = pct >= 80 ? 'bg-success' : (pct >= 40 ? 'bg-warning' : 'bg-danger');
        return '<div class="d-flex align-items-center" style="gap:6px;min-width:140px;">' +
                 '<div class="progress flex-grow-1" style="height:8px;">' +
                    '<div class="progress-bar ' + clase + '" role="progressbar" style="width:' + pct + '%"></div>' +
                 '</div>' +
                 '<small class="text-muted">' + ok + '/' + total + '</small>' +
               '</div>';
    }

    // ----- Inicializacion DataTable (client-side, data via ajax POST) -----
    var tblCampanas = $('#tblCampanas').DataTable({
        ajax: {
            url: '{{ route('campains.list') }}',
            type: 'POST',
            data: function (d) {
                d.fechaInicio = $('#filtroFechaInicio').val() || null;
                d.fechaFin    = $('#filtroFechaFin').val() || null;
            },
            dataSrc: function (json) {
                var rows = json.data || [];
                $('#lblTotalCampanas').text(rows.length + ' campañas');
                return rows;
            }
        },
        processing: true,
        deferRender: true,
        scrollX: false,
        ordering: false,
        responsive: true,
        lengthMenu: [[20, 50, 100, 200], [20, 50, 100, 200]],
        pageLength: 20,
        language: {
            loadingRecords: 'Cargando...',
            processing:     'Cargando...',
            emptyTable:     'No tienes campañas creadas',
            zeroRecords:    'No existen registros que coincidan',
            search:         'Buscar: ',
            lengthMenu:     '_MENU_ registros',
            info:           'Mostrando _START_ al _END_ de _MAX_ total registros',
            infoEmpty:      'No se encontraron registros',
            infoFiltered:   '(filtrado desde _MAX_ total registros)',
            infoPostFix:    '',
            thousands:      ',',
            paginate:       { first: 'Primero', last: 'Último', next: 'Sig', previous: 'Prev' }
        },
        columns: [
            { data: null,                  defaultContent: '-', render: function (row, type, r) {
                var id = r.IdCampaign || '';
                return '<a href="#" class="text-body js-open-detalle" data-idcampaign="' + id + '" data-name="' + $('<div>').text(r.Name||'').html() + '">' + nombreCol(r) + '</a>';
            }},
            { data: 'ScheduleType',        defaultContent: '-', render: function (v) { return badgeTipoEnvio(v); } },
            { data: 'Inicio',              defaultContent: '-', render: function (v) { return v ? fmtFecha(v) : '-'; } },
            { data: 'Fin',                 defaultContent: '-', render: function (v) { return v ? fmtFecha(v) : '-'; } },
            { data: 'Estado',              defaultContent: '-', render: function (v) { return badgeEstado(v); } },
            { data: 'Segmentacion',        defaultContent: '-', render: function (v) { return badgeSegmentacion(v); } },
            { data: null,                  defaultContent: '-', render: function (row, type, r) { return entregaCol(r); } },
            { data: 'UltimaActualizacion', defaultContent: '-', render: function (v) { return v ? fmtFecha(v) : '-'; } },
            { data: 'CreatedBy',           defaultContent: '-' },
        ],
    });

    // ================= Drill-down: abrir modal con metricas + detalle =================
    var currentIdCampaign = null;
    var currentPage       = 1;
    var pageSize          = 50;

    function cargarMetricas(idCampaign) {
        $.post('{{ route('campains.metricas') }}', { idCampaign: idCampaign })
         .done(function (resp) {
            var m = resp.metricas || {};
            $('#met-AudienciaReal').text(m.AudienciaReal || 0);
            $('#met-Enviadas').text(m.Enviadas || 0);
            $('#met-Confirmadas').text(m.Confirmadas || 0);
            $('#met-Pendientes').text(m.Pendientes || 0);
            $('#met-Fallidas').text(m.Fallidas || 0);
            $('#met-Entregadas').text(m.Entregadas || 0);
         });
    }

    function cargarDetalle(idCampaign, page) {
        var estadoPush = $('#filtroEstadoPush').val() || null;
        $.post('{{ route('campains.detalle') }}', {
            idCampaign: idCampaign,
            estadoPush: estadoPush,
            page:       page,
            pageSize:   pageSize
        }).done(function (resp) {
            var rows  = resp.data  || [];
            var total = resp.total || 0;
            var $body = $('#tblDetalleEntregaBody').empty();

            if (rows.length === 0) {
                $body.append('<tr><td colspan="6" class="text-center text-muted">Sin datos</td></tr>');
            } else {
                rows.forEach(function (r) {
                    $body.append(
                        '<tr>' +
                          '<td>' + (r.Min || '-') + '</td>' +
                          '<td><code>' + (r.DId || '-') + '</code></td>' +
                          '<td>' + (r.App || '-') + '</td>' +
                          '<td>' + (r.SecuenciaAlerta || '-') + '</td>' +
                          '<td>' + badgeEstadoPush(r.EstadoPush) + '</td>' +
                          '<td>' + (r.FechaPush ? fmtFecha(r.FechaPush) : '-') + '</td>' +
                        '</tr>'
                    );
                });
            }

            var from = total === 0 ? 0 : ((page - 1) * pageSize + 1);
            var to   = Math.min(page * pageSize, total);
            $('#lblDetallePaginacion').text('Mostrando ' + from + ' al ' + to + ' de ' + total + ' registros');
            $('#btnDetallePrev').prop('disabled', page <= 1);
            $('#btnDetalleNext').prop('disabled', to >= total);
        });
    }

    function badgeEstadoPush(e) {
        // Paleta consistente con los KPIs del modal.
        // APP=Pendiente (gris) / SEN=Enviado (celeste) / FIN=Confirmado (verde) / N|NOS=Fallido (rojo)
        switch (e) {
            case 'APP': return '<span class="badge bg-secondary">Pendiente</span>';
            case 'SEN': return '<span class="badge bg-info">Enviado</span>';
            case 'FIN': return '<span class="badge bg-success">Confirmado</span>';
            case 'N':
            case 'NOS': return '<span class="badge bg-danger">Fallido</span>';
            default:    return '<span class="badge bg-light text-dark">' + (e || 'Sin registro') + '</span>';
        }
    }

    $('#tblCampanas tbody').on('click', '.js-open-detalle', function (e) {
        e.preventDefault();
        currentIdCampaign = $(this).data('idcampaign');
        currentPage       = 1;
        $('#modalEntregaCampana').text($(this).data('name') || '');
        $('#filtroEstadoPush').val('');
        cargarMetricas(currentIdCampaign);
        cargarDetalle(currentIdCampaign, currentPage);
        new bootstrap.Modal(document.getElementById('modalEntrega')).show();
    });

    // Inicializar tooltips BS5 una vez que el modal se muestre
    // (los iconos ? de los KPIs viven dentro del modal y recien existen en DOM
    //  tras el show() - bastaria una vez, pero lo hacemos en cada show por si
    //  el DOM se regenerara).
    $('#modalEntrega').on('shown.bs.modal', function () {
        var tooltipTriggerList = [].slice.call(
            document.querySelectorAll('#modalEntrega [data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.forEach(function (el) {
            // Evitar duplicados si ya existe instancia
            var existing = bootstrap.Tooltip.getInstance(el);
            if (!existing) { new bootstrap.Tooltip(el); }
        });
    });

    $('#filtroEstadoPush').on('change', function () {
        currentPage = 1;
        cargarDetalle(currentIdCampaign, currentPage);
    });

    $('#btnDetallePrev').on('click', function () {
        if (currentPage > 1) {
            currentPage--;
            cargarDetalle(currentIdCampaign, currentPage);
        }
    });

    $('#btnDetalleNext').on('click', function () {
        currentPage++;
        cargarDetalle(currentIdCampaign, currentPage);
    });

    $('#btnAplicarFiltro').on('click', function () {
        tblCampanas.ajax.reload();
    });

    $('#btnLimpiarFiltro').on('click', function () {
        $('#filtroFechaInicio').val('');
        $('#filtroFechaFin').val('');
        tblCampanas.ajax.reload();
    });
});
</script>
@endpush
