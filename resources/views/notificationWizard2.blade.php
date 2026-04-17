@extends('layouts.app')

@section('title', '| Nueva notificacion')

@push('css')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <style>
        .wizard-step-content { min-height: 400px; }
        .preview-device {
            width: 280px; margin: 0 auto;
            border: 8px solid #222; border-radius: 30px;
            overflow: hidden; background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .preview-device .notif-preview {
            padding: 14px; min-height: 200px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .preview-device .notif-image { width: 100%; border-radius: 8px; }
        .device-row.selected { background-color: rgba(var(--bs-primary-rgb), 0.1); }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Asistente de envio</h4>
                    <p class="card-title-desc">
                        App actual: <strong>{{ session('AppNotify.name') }}</strong>
                        &middot; Pais: <strong>{{ session('Pais') }}</strong>
                    </p>

                    <form id="wizard-form" enctype="multipart/form-data">
                        @csrf
                        <div id="notif-wizard" class="twitter-bs-wizard">
                            <ul class="twitter-bs-wizard-nav nav-justified nav nav-pills">
                                <li class="nav-item"><a href="#step-design" class="nav-link active" data-bs-toggle="tab">
                                    <span class="step-number">01</span><span class="step-title">Contenido</span>
                                </a></li>
                                <li class="nav-item"><a href="#step-audience" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number">02</span><span class="step-title">Audiencia</span>
                                </a></li>
                                <li class="nav-item"><a href="#step-schedule" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number">03</span><span class="step-title">Programacion</span>
                                </a></li>
                                <li class="nav-item"><a href="#step-review" class="nav-link" data-bs-toggle="tab">
                                    <span class="step-number">04</span><span class="step-title">Revision</span>
                                </a></li>
                            </ul>

                            <div class="tab-content twitter-bs-wizard-tab-content wizard-step-content mt-4">

                                {{-- STEP 1: Contenido --}}
                                <div class="tab-pane active" id="step-design">
                                    <div class="row">
                                        <div class="col-lg-7">
                                            <div class="mb-3">
                                                <label class="form-label">Tipo de notificacion</label>
                                                <select id="tipoNoti" name="tipoNoti" class="form-select">
                                                    <option value="0">Informativa (imagen + texto)</option>
                                                    <option value="1">Multimedia (imagen + texto + botones)</option>
                                                    <option value="2">HTML (landing page)</option>
                                                    <option value="4">Texto plano</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nombre de la campana (opcional)</label>
                                                <input type="text" class="form-control" name="campaignName" maxlength="200" placeholder="Ej. Mantenimiento programado abril">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Titulo</label>
                                                <input type="text" class="form-control" name="titleNoti" id="titleNoti" maxlength="200">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Subtitulo / cuerpo</label>
                                                <textarea class="form-control" name="subTitleNoti" id="subTitleNoti" rows="3" maxlength="500"></textarea>
                                            </div>

                                            <div class="mb-3" data-show-for="0,1,2">
                                                <label class="form-label">Imagen (PNG/JPG, max {{ (int) (config('global.upload_max_kb')/1024) }}MB)</label>
                                                <input type="file" class="form-control" name="imagen" id="imagenInput" accept="image/png,image/jpeg">
                                            </div>

                                            <div class="mb-3" data-show-for="2">
                                                <label class="form-label">Archivo HTML</label>
                                                <input type="file" class="form-control" name="html" id="htmlInput" accept=".html,.htm">
                                            </div>

                                            <div class="mb-3" data-show-for="1,2">
                                                <label class="form-label">URL de destino (boton)</label>
                                                <input type="url" class="form-control" name="urlNoti" placeholder="https://...">
                                            </div>
                                        </div>

                                        <div class="col-lg-5">
                                            <p class="text-muted mb-2 text-center">Vista previa</p>
                                            <div class="preview-device">
                                                <div class="notif-preview" id="notifPreview">
                                                    <img id="previewImg" class="notif-image d-none" src="" alt="">
                                                    <strong id="previewTitle" class="font-size-14"></strong>
                                                    <span id="previewSubtitle" class="text-muted font-size-12"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- STEP 2: Audiencia --}}
                                <div class="tab-pane" id="step-audience">
                                    <p class="text-muted">Selecciona los dispositivos destinatarios. Puedes filtrar por plataforma, tipo de entidad o grupo.</p>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Plataforma</label>
                                            <select id="filterPlataforma" class="form-select">
                                                <option value="">Todas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Tipo de entidad</label>
                                            <select id="filterTipoEnt" class="form-select">
                                                <option value="0">Todos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Grupo</label>
                                            <select id="filterGrupo" class="form-select">
                                                <option value="0">Todos</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnFilter">
                                            <i class="ri-filter-3-line me-1"></i> Aplicar filtros
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSelectAll">
                                            <i class="ri-checkbox-multiple-line me-1"></i> Seleccionar todos
                                        </button>
                                        <span class="ms-3 text-muted"><span id="selectedCount">0</span> seleccionados</span>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="audience-tbl" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px;"><input type="checkbox" id="chkAll"></th>
                                                    <th>Usuario</th>
                                                    <th>Min</th>
                                                    <th>Plataforma</th>
                                                    <th>Modelo</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                                {{-- STEP 3: Programacion --}}
                                <div class="tab-pane" id="step-schedule">
                                    <p class="text-muted">Elige cuando enviar la notificacion.</p>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="program" id="prog0" value="0" checked>
                                            <label class="form-check-label" for="prog0"><strong>Ahora</strong> (envio inmediato)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="program" id="prog1" value="1">
                                            <label class="form-check-label" for="prog1"><strong>Una vez</strong> en fecha especifica</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="program" id="prog2" value="2">
                                            <label class="form-check-label" for="prog2"><strong>Diario</strong> a la misma hora</label>
                                        </div>
                                    </div>

                                    <div id="scheduleOnce" class="d-none">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" name="scheduleDate">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Hora</label>
                                                <input type="time" class="form-control" name="scheduleTime">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="scheduleDaily" class="d-none">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Hora diaria</label>
                                                <input type="time" class="form-control" name="dailyTime">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Desde</label>
                                                <input type="date" class="form-control" name="dailyStartDate">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Hasta (opcional)</label>
                                                <input type="date" class="form-control" name="dailyEndDate">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- STEP 4: Revision --}}
                                <div class="tab-pane" id="step-review">
                                    <p class="text-muted">Revisa antes de enviar.</p>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <dl class="row mb-0" id="reviewSummary">
                                                <dt class="col-sm-3">Tipo</dt>   <dd class="col-sm-9" id="rev-tipo">-</dd>
                                                <dt class="col-sm-3">Titulo</dt> <dd class="col-sm-9" id="rev-title">-</dd>
                                                <dt class="col-sm-3">Audiencia</dt><dd class="col-sm-9" id="rev-audience">-</dd>
                                                <dt class="col-sm-3">Cuando</dt> <dd class="col-sm-9" id="rev-when">-</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <ul class="pager wizard twitter-bs-wizard-pager-link mt-4">
                                <li class="previous"><a href="javascript:;" class="btn btn-outline-secondary"><i class="ri-arrow-left-line me-1"></i> Anterior</a></li>
                                <li class="next"><a href="javascript:;" class="btn btn-primary">Siguiente <i class="ri-arrow-right-line ms-1"></i></a></li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
/* HMNotify Wizard v2 - flujo SSO. */
(function () {
    'use strict';
    const csrf = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });

    let audienceTable = null;
    let selectedTargets = new Map(); // key: Min, value: {Name, DId, App, Min}

    // ---- STEP 1: toggles + preview ----
    function updateFieldVisibility() {
        const v = $('#tipoNoti').val();
        $('[data-show-for]').each(function () {
            const allowed = $(this).data('show-for').toString().split(',');
            $(this).toggle(allowed.includes(v));
        });
    }

    function updatePreview() {
        $('#previewTitle').text($('#titleNoti').val());
        $('#previewSubtitle').text($('#subTitleNoti').val());
        const file = $('#imagenInput')[0].files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $('#previewImg').attr('src', e.target.result).removeClass('d-none');
            reader.readAsDataURL(file);
        }
    }

    $('#tipoNoti').on('change', updateFieldVisibility);
    $('#titleNoti, #subTitleNoti').on('input', updatePreview);
    $('#imagenInput').on('change', updatePreview);
    updateFieldVisibility();

    // ---- STEP 2: cargar catalogos + tabla audiencia ----
    function loadCatalogos() {
        $.get('{{ route('api.catalogos') }}').done(function (resp) {
            (resp.Platforms || []).forEach(p => $('#filterPlataforma').append(
                $('<option>').val(p.Platform).text(p.Platform)));
            (resp.EntTypes || []).forEach(t => $('#filterTipoEnt').append(
                $('<option>').val(t.EntType).text(t.EntName)));
            (resp.Groups || []).forEach(g => $('#filterGrupo').append(
                $('<option>').val(g.GroupId).text(g.GroupName)));
        });
    }

    function loadAudience(useFilters) {
        const url     = useFilters ? '{{ route('api.dispositivos-alt') }}' : '{{ route('api.dispositivos') }}';
        const payload = useFilters ? {
            plataforma: $('#filterPlataforma').val() || null,
            idTipoEnt:  $('#filterTipoEnt').val(),
            idSubGrupo: $('#filterGrupo').val(),
            filtroTipoUser: 3,
        } : {};

        $.post(url, payload).done(function (resp) {
            const rows = (resp.Devices || []).map(function (r) {
                r.__min = r.Min || '';
                return r;
            });

            if (audienceTable) audienceTable.destroy();
            $('#audience-tbl tbody').empty();

            audienceTable = $('#audience-tbl').DataTable({
                data: rows,
                columns: [
                    { data: null, orderable: false, render: function (d) {
                        const checked = selectedTargets.has(d.__min) ? 'checked' : '';
                        return `<input type="checkbox" class="chk-device" data-min="${d.__min}" ${checked}>`;
                    }},
                    { data: 'User',     defaultContent: '-' },
                    { data: 'Min',      defaultContent: '-' },
                    { data: 'Platform', defaultContent: '-' },
                    { data: 'DevModel', defaultContent: '-' },
                ],
                pageLength: 25,
                language: { search: 'Buscar:', emptyTable: 'Sin dispositivos', zeroRecords: 'Sin resultados' },
                createdRow: function (row, data) {
                    if (selectedTargets.has(data.__min)) $(row).addClass('selected');
                    $(row).find('.chk-device').on('change', function () {
                        const min = $(this).data('min');
                        if (this.checked) {
                            selectedTargets.set(min, { Name: data.User || '', DId: data.Vid || '', App: {{ session('AppNotify.idLocal', 1) }}, Min: min });
                            $(row).addClass('selected');
                        } else {
                            selectedTargets.delete(min);
                            $(row).removeClass('selected');
                        }
                        $('#selectedCount').text(selectedTargets.size);
                    });
                }
            });
            $('#selectedCount').text(selectedTargets.size);
        });
    }

    $('#btnFilter').on('click', () => loadAudience(true));
    $('#btnSelectAll').on('click', function () {
        $('#audience-tbl .chk-device').each(function () {
            if (!this.checked) { this.checked = true; $(this).trigger('change'); }
        });
    });

    // ---- STEP 3: radio toggle ----
    $('input[name="program"]').on('change', function () {
        const v = $(this).val();
        $('#scheduleOnce').toggleClass('d-none', v !== '1');
        $('#scheduleDaily').toggleClass('d-none', v !== '2');
    });

    // ---- STEP 4: revision ----
    function updateReview() {
        $('#rev-tipo').text($('#tipoNoti option:selected').text());
        $('#rev-title').text($('#titleNoti').val() || '-');
        $('#rev-audience').text(selectedTargets.size + ' dispositivos');
        const prog = $('input[name="program"]:checked').val();
        let when = 'Ahora';
        if (prog === '1') when = 'Una vez: ' + $('[name=scheduleDate]').val() + ' ' + $('[name=scheduleTime]').val();
        if (prog === '2') when = 'Diario a las ' + $('[name=dailyTime]').val();
        $('#rev-when').text(when);
    }

    // ---- Wizard navigation manual (simple tab switching) ----
    const tabs = ['#step-design', '#step-audience', '#step-schedule', '#step-review'];
    function currentIdx() {
        return tabs.findIndex(t => $(t).hasClass('active'));
    }
    function goTo(idx) {
        if (idx < 0 || idx >= tabs.length) return;
        tabs.forEach((t, i) => {
            $(`[href="${t}"]`).toggleClass('active', i === idx);
            $(t).toggleClass('active show', i === idx);
        });
        if (idx === 1 && !audienceTable) loadAudience(false);
        if (idx === 3) updateReview();

        $('.pager .previous a').toggleClass('disabled', idx === 0);
        $('.pager .next a').text(idx === tabs.length - 1 ? 'Enviar' : 'Siguiente')
            .toggleClass('btn-primary', true);
    }

    $('.pager .next a').on('click', function () {
        const idx = currentIdx();
        if (idx === tabs.length - 1) {
            submitWizard();
        } else {
            goTo(idx + 1);
        }
    });
    $('.pager .previous a').on('click', function () { goTo(currentIdx() - 1); });
    $('.twitter-bs-wizard-nav a').on('shown.bs.tab', function () {
        const href = $(this).attr('href');
        const idx  = tabs.indexOf(href);
        if (idx === 1 && !audienceTable) loadAudience(false);
        if (idx === 3) updateReview();
    });

    // ---- Submit ----
    function submitWizard() {
        if (selectedTargets.size === 0) {
            Swal.fire({ icon: 'warning', title: 'Sin destinatarios', text: 'Selecciona al menos 1 dispositivo.' });
            return;
        }

        const fd = new FormData(document.getElementById('wizard-form'));
        Array.from(selectedTargets.values()).forEach((t, i) => {
            fd.append(`targets[${i}][Name]`, t.Name);
            fd.append(`targets[${i}][DId]`,  t.DId);
            fd.append(`targets[${i}][App]`,  t.App);
            fd.append(`targets[${i}][Min]`,  t.Min);
        });

        Swal.fire({ title: 'Enviando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

        $.ajax({
            url: '{{ route('wizard.send') }}', method: 'POST', data: fd,
            processData: false, contentType: false,
        }).done(function (resp) {
            Swal.close();
            if (!resp || resp.Error) {
                Swal.fire({ icon: 'error', title: 'Error', text: (resp && resp.Mensaje) || 'No se pudo enviar.' });
                return;
            }
            Swal.fire({
                icon: 'success', title: 'Enviada!',
                text: `Send #${resp.Wizard?.SendId || '-'} creado${resp.Wizard?.CampaignId ? ` (Campana #${resp.Wizard.CampaignId})` : ''}.`,
            }).then(() => window.location = '{{ route('dashboard') }}');
        }).fail(function (xhr) {
            Swal.close();
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Error de servidor';
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        });
    }

    loadCatalogos();
    goTo(0);
})();
</script>
@endpush
