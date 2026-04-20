@extends('layouts.app')

@section('title', '| Dashboard')

@push('css')
    <link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">App actual</p>
                            <h4 class="mb-0" id="stat-app-name">{{ session('AppNotify.name', '-') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title"><i class="ri-apps-2-line font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">País</p>
                            <h4 class="mb-0">{{ session('Pais', 'EC') }}</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title"><i class="ri-earth-line font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Dispositivos activos</p>
                            <h4 class="mb-0" id="stat-total">-</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title"><i class="ri-smartphone-line font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Dispositivos con push activo</h4>
                        <a href="{{ route('wizard') }}" class="btn btn-primary btn-sm">
                            <i class="ri-mail-send-line me-1"></i> Nueva notificación
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="dispositivos-tbl" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Min</th>
                                    <th>Vid</th>
                                    <th>Plataforma</th>
                                    <th>Modelo</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(function () {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });

        // El cache client-side compartido se quito: 35k filas no caben en sessionStorage.
        // Dashboard y wizard consultan el mismo endpoint; el cache de Laravel (120s por
        // app/payload) responde al segundo hit en ms sin pegar HMSrvAuth.
        function renderDashboardTable(rows) {
            $('#stat-total').text(rows.length);
            $('#dispositivos-tbl').DataTable({
                data: rows,
                columns: [
                    { data: 'Name',  defaultContent: '-' },
                    { data: 'Min',   defaultContent: '-' },
                    { data: 'DId',   defaultContent: '-' },
                    { data: 'Plat',  defaultContent: '-' },
                    { data: 'MDisp', defaultContent: '-' },
                ],
                pageLength: 25,
                deferRender: true,
                search: { return: true }, // Enter-only (35k+ registros)
                language: {
                    search:      'Buscar (Enter):',
                    lengthMenu:  'Mostrar _MENU_ registros',
                    info:        'Mostrando _START_ a _END_ de _TOTAL_',
                    paginate:    { next: 'Siguiente', previous: 'Anterior' },
                    emptyTable:  'Sin dispositivos activos',
                    zeroRecords: 'Sin resultados',
                }
            });
        }

        console.time('[dashboard] ajax api.dispositivos-alt');
        $.post('{{ route('api.dispositivos-alt') }}', {
            plat: 'TODOS', idTipoEnt: null, filtroTipoUser: 3, idSubGrupo: null,
            template: 'NORMAL', chasisList: '', motorList: '', numList: ''
        }).done(function (resp) {
            console.timeEnd('[dashboard] ajax api.dispositivos-alt');
            var rows = (resp && resp.data) ? resp.data : [];
            console.log('[dashboard] respuesta ajax:', rows.length, 'filas');
            renderDashboardTable(rows);
        }).fail(function (xhr) {
            console.timeEnd('[dashboard] ajax api.dispositivos-alt');
            console.error('[dashboard] ajax fallo', xhr && xhr.status, xhr && xhr.responseText);
            alert('No se pudo consultar dispositivos.');
        });
    });
</script>
@endpush
