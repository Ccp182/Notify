var initialDatatableDisp=true;
var dispositivos=null;
let activeFilter=false;
let template=false;
$(document).ready(function () {
    

    
    $('#ordercheck').on('change', function () {
        let isChecked = $(this).prop('checked');
        $('#datatable-dispositivos-alt').off('select.dt deselect.dt');
        if (isChecked) {
            tableData.rows().select(); // Seleccionar todas las filas
        } else {
            tableData.rows().deselect(); // Deseleccionar todas las filas
        }
        $('#datatable-dispositivos-alt tbody .form-check-input').prop('checked', isChecked);
        eventClickCheckDataTable();
        
    });

    $('#grupos').multipleSelect({
        width: '100%',
        selectAllText: 'Todos',
        selectAllDelimiter: '',
        allSelected: 'Todos',
        maxOptions: 10000,
        countSelected: '# de % seleccionados',
        minimumCountSelected: 1,
        noMatchesFound: 'No encontrado',
        filter: true,
    });

    $('#idTipoEnt').multipleSelect({
        width: '100%',
        selectAllText: 'Todos',
        selectAllDelimiter: '',
        allSelected: 'Todos',
        maxOptions: 10000,
        countSelected: '# de % seleccionados',
        minimumCountSelected: 1,
        noMatchesFound: 'No encontrado',
        filter: true,
    });
    //let table = setDatatableDispositivosAlt(); // Inicializa DataTable

    
});



// Helpers para cache client-side en sessionStorage
function _isEmpty(v) {
    if (v == null) return true;
    if (Array.isArray(v)) return v.length === 0;
    if (typeof v === 'string') return v === '' || v === 'null' || v === 'TODOS';
    return false;
}
// "todas las options seleccionadas" == equivalente a "sin filtro".
// Esto normaliza los multiselect que vienen pre-seleccionados por defecto.
function _allSelected(selectSel) {
    var $s = $(selectSel);
    if (!$s.length) return true;
    var total = $s.find('option').length;
    var val = $s.val();
    var sel = Array.isArray(val) ? val.length : (val ? 1 : 0);
    return total > 0 && sel >= total;
}
function _normalizedFilterValue(selectSel) {
    // Si todas estan seleccionadas, tratamos como null (sin filtro) para que
    // el cache key sea IGUAL al del dashboard y el backend devuelva mismo cache.
    if (_allSelected(selectSel)) return null;
    var v = $(selectSel).val();
    return _isEmpty(v) ? null : v;
}
function _dispoCacheParams() {
    return {
        plat: _isEmpty($('#plataforma').val()) ? null : $('#plataforma').val(),
        idTipoEnt: _normalizedFilterValue('#idTipoEnt'),
        filtroTipoUser: $('#tipoUser').val(),
        idSubGrupo: _normalizedFilterValue('#grupos'),
        template: template?'TEMPLATE':'NORMAL',
        chasisList: $('#inputChasisList').val() || '',
        motorList: $('#inputMotorList').val() || '',
        numList: $('#inputNumList').val() || ''
    };
}
function _dispoNoFilters() {
    var p = _dispoCacheParams();
    var no = (p.idTipoEnt == null) && (p.idSubGrupo == null) &&
             (p.template === 'NORMAL') &&
             _isEmpty(p.chasisList) && _isEmpty(p.motorList) && _isEmpty(p.numList);
    console.log('[dispo] _dispoNoFilters=', no, 'params=', p);
    return no;
}
function _dispoCacheKey() {
    try { return 'hmnotify:dispo:'+btoa(unescape(encodeURIComponent(JSON.stringify(_dispoCacheParams())))); }
    catch (e) { return null; }
}
// Cache client-side deshabilitado: con 35k+ filas el sessionStorage (~5MB) revienta.
// El cache de Laravel (120s, file driver) responde al segundo hit en ms sin pegar HMSrvAuth.
function _dispoCacheRead(_maxAgeMs) { return null; }
function _dispoCacheWrite(_data) { /* no-op */ }

function setDatatableDispositivosAlt(){
    if (!$.fn.DataTable.isDataTable('#datatable-dispositivos-alt')) {
        // 1) Intentar servir desde sessionStorage (TTL 120s) -> init sin ajax
        var cached = _dispoCacheRead(120000);
        var dtConfig = {
            language: {
                "loadingRecords": "&nbsp;",
                //"processing": "Cargando...",
                "emptyTable":     "No hay datos disponibles",
                "info":           "Mostrando _START_ al _END_ de _MAX_ total registros",
                "infoEmpty":      "No se encontraron registros",
                "infoFiltered":   "(filtrado desde _MAX_ total registros)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "_MENU_ registros",
                "loadingRecords": "Cargando...", 
                "search":         "Buscar: ",
                "zeroRecords":    "No existen registros que coincidan",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Último",
                    "next":       "Sig",
                    "previous":   "Prev"
                },
            },
            buttons: false,
            responsive: true,
            processing: true,
            deferRender: true,        // solo renderiza filas de la pagina actual (mucho mas rapido)
            scrollX: false,
            select: true,
            rowId: 'Id',
            select: 'multi',
            ajax: {
                url: postDispositivos,
                type:"POST",
                cache: true,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (d) {
                    // Normalizamos multiselects con "todos seleccionados" a null para que
                    // los params (y por ende el cache key del backend) coincidan con el dashboard.
                    var p = _dispoCacheParams();
                    d.plat           = p.plat || 'TODOS';
                    d.idTipoEnt      = p.idTipoEnt;
                    d.filtroTipoUser = p.filtroTipoUser;
                    d.idSubGrupo     = p.idSubGrupo;
                    d.template       = p.template;
                    d.chasisList     = p.chasisList;
                    d.motorList      = p.motorList;
                    d.numList        = p.numList;
                },
                // Cache client-side por filtros (TTL 120s, coincide con server)
                dataSrc: function (json) {
                    _dispoCacheWrite(json.data || []);
                    return json.data || [];
                }
            },
            
            rowId: 'Id',
            drawCallback: function(row, data) {
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                $(rows).each((index, rowElement) => {
                    let row = api.row(index); // Obtener la fila de DataTables usando el índice
                    let checkbox = $(rowElement).find('.form-check-input'); // Buscar el checkbox en la fila

                    if (row.any() && row.selected()) { // Verifica si la fila existe y está seleccionada
                        checkbox.prop('checked', true);
                    } else {
                        checkbox.prop('checked', false);
                    }
                });
                
            },
            //ordering: false,
            lengthMenu: [[15, 25, 50, 100, 200], [15, 25, 50, 100, 200]],
            columns: [
                { data: 'Check', visible: true, searchable: false, orderable: false },
                { data: 'App', visible: false, searchable: false, orderable: false },
                { data: 'DId', visible: false, searchable: false, orderable: false },
                { data: 'Name', visible: false, searchable: false, orderable: false },
                { data: 'NameExt', visible: true, searchable: true, orderable: true, render: function(data, type, row) {
                    // En sort/filter retornamos solo el string: DataTables no re-genera HTML por fila.
                    if (type === 'filter' || type === 'sort' || type === 'type') return data[0];
                    return getBadgeForName(data);
                } },
                { data: 'NGroup', visible: true, searchable: true, orderable: true },
                { data: 'Min', visible: true, searchable: true, orderable: false,
                  render: function(data, type) { return (type === 'display') ? data : String(data || '').toLowerCase(); } },
                { data: 'Plat', visible: true, searchable: false, orderable: true, render: function(data, type, row) {
                    if (type !== 'display') return data;
                    return getBadgeForApp(data);
                } },
                { data: 'MDisp', visible: true, searchable: false, orderable: true },
                { data: 'Tipo', visible: true, searchable: true, orderable: true,
                  render: function(data, type) { return (type === 'display') ? data : String(data || '').toLowerCase(); } },
                
            ],
            order: [[3, 'asc']],
            // 35k+ registros: buscar en vivo bloquea el navegador.
            // Usar Enter para ejecutar la busqueda.
            search: {
                return: true
            },
            initComplete: function (settings, json) { /* no-op */ }
        };

        console.log('[dispo] === setDatatableDispositivosAlt INICIO === AJAX al backend (cache Laravel 120s)');

        tableData = $('#datatable-dispositivos-alt').DataTable(dtConfig)
            .on('xhr.dt', function (e, settings, json) {
                if (json && json.data) {
                    console.log('[dispo] xhr.dt recibido, ' + json.data.length + ' filas');
                }
            })
            .on('error.dt', function (e, settings, techNote, message) {
                console.warn('[dispo] DataTable error:', message);
            });

       
    }else{
        if(activeFilter){
            $('#datatable-dispositivos-alt').DataTable().clear().draw();
            $('#datatable-dispositivos-alt').DataTable().ajax.reload();
            activeFilter=false;
        }
       
    }


    // Elimina cualquier evento previo para evitar múltiples registros
    eventClickCheckDataTable();

}

function eventClickCheckDataTable(){
    $('#datatable-dispositivos-alt').off('select.dt deselect.dt').on('select.dt deselect.dt', function (e, dt, type, indexes) {
        if (type === 'row') {
            indexes.forEach(index => {
                let row = dt.row(index); // Usa `dt` en lugar de `tableData.row(index)`
                let checkbox = $(row.node()).find('.form-check-input');

                if (row.selected()) {
                    checkbox.prop('checked', true); // Marcar checkbox
                } else {
                    checkbox.prop('checked', false); // Desmarcar checkbox
                }
            });
        }
    });
}

function getBadgeForApp(app) {
    if (app === 'ANDROID') {
        return '<div class="badge badge-soft-success font-size-12"><i class="mdi mdi-android font-size-11"></i> Android</div>';
    } else if (app === 'IOS') {
        return '<div class="badge badge-soft-secondary font-size-12"><i class="mdi mdi-apple font-size-11"></i> Ios</div>';
    }
    return ''; // Si no es ANDROID ni IOS, retorna vacío
}

function getBadgeForName(NameExt) {
    return (NameExt[1]!='SUBUSUARIO'?'<div class="badge badge-soft-light font-size-12" title="" style="background-color:#F2F3F4;"><i class="mdi mdi-account font-size-11" style="color:#96989c;"></i></div> ':'<div class="badge badge-soft-info font-size-12" title=""><i class="mdi mdi-account-supervisor font-size-11"></i></div> ')+NameExt[0];
}