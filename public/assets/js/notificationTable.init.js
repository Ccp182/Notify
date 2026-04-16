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



function setDatatableDispositivosAlt(){
    if (!$.fn.DataTable.isDataTable('#datatable-dispositivos-alt')) {
        tableData =$('#datatable-dispositivos-alt').DataTable({
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
            //deferRender: true,
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
                    d.plat=$('#plataforma').val(),
                    d.idTipoEnt=$('.select_tipo_entidad .ms-choice span').html()=='TODOS'?null:$('#idTipoEnt').val(),
                    d.filtroTipoUser=$('#tipoUser').val(),
                    d.idSubGrupo=$('.select_grupos .ms-choice span').html()=='TODOS'?null:$('#grupos').val(),
                    d.template = template?'TEMPLATE':'NORMAL';
                    d.chasisList = $('#inputChasisList').val();
                    d.motorList = $('#inputMotorList').val();
                    d.numList = $('#inputNumList').val();
                },
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
                    return getBadgeForName(data);
                } },
                { data: 'NGroup', visible: true, searchable: true, orderable: true },
                { data: 'Min', visible: true, searchable: true, orderable: false},
                { data: 'Plat', visible: true, searchable: false, orderable: true, render: function(data, type, row) {
                    return getBadgeForApp(data);
                } },
                { data: 'MDisp', visible: true, searchable: false, orderable: true },
                { data: 'Tipo', visible: true, searchable: true, orderable: true},
                
            ],
            order: [[3, 'asc']],
            search: {
                delay: 300,
                smartSearch: true,
                return: true
            },
            initComplete: function (settings, json) { 
                /*if(initialDatatableDisp){
                    initDatatableDispOption();
                    initialDatatableDisp=false;
                }*/
            }
        }).on('xhr.dt', function ( e, settings, json, xhr ) {
            console.log('work...!!!');

        })   
        .on('error.dt', function ( e, settings, techNote, message ) {
            console.log('Uh-oh, that did not work...'); 
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