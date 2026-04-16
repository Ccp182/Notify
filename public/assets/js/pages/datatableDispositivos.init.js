var initialDatatableDisp=true;
var dispositivos=null;
function setDatatableDispositivos(){
    return $('#datatable-dispositivos').DataTable({
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
        deferRender: true,
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
            "data": function ( d ) {
                
            },
        },
        
        rowId: 'Id',
        drawCallback: function(row, data) {
            
        },
        ordering: false,
        lengthMenu: [[20, 50, 100, 200], [20, 50, 100, 200]],
        columns: [
            { data: 'App', visible: false, searchable: false, orderable: false },
            { data: 'DId', visible: true, searchable: false, orderable: false },
            { data: 'Usu', visible: true, searchable: true, orderable: true },
            { data: 'Min', visible: true, searchable: true, orderable: false},
            { data: 'Plat', visible: true, searchable: false, orderable: false },
            { data: 'MDisp', visible: true, searchable: false, orderable: false },
            
        ],
        search: {
            delay: 300,
            smartSearch: true,
            return: true
        },
        initComplete: function (settings, json) { 
            if(initialDatatableDisp){
                initDatatableDispOption();
                initialDatatableDisp=false;
            }
        }
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        console.log('work...!!!');

    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); 
    });
}


function initDatatableDispOption(){
    $('#sendNotificationfrm').on('reset', function() {
        $('#sendNotificationfrm .form-control').prop('disabled', true);
        $('#sendNotificationfrm .form-control').prop('required', false);
        $('#title-input').prop('disabled', false);
        $('#subtitle-input').prop('disabled', false);
        $('#title-input').prop('required', true);
        $('#subtitle-input').prop('required', true);
        $('#url-input').prop('disabled', false);
        $('#date-input').prop('disabled', false);
        $('#customFileNotification').prop('disabled', false);
        $('#url-input').prop('required', false);
        $('#date-input').prop('required', false);
        $('#fcustomFileNotification').prop('required', true);
        dispositivos.rows().deselect();
    });
    $('#sendNotificationfrm').submit(function(event) {
        // Detener el comportamiento predeterminado de envío del formulario
        event.preventDefault();

       


        Swal.fire({
            title: 'Atención',
            text: '¿Está seguro que desea enviar esta(s) notificacion(es)?',
            icon: 'question', // Puedes personalizar el ícono (por ejemplo, 'question', 'warning', 'info', etc.)
            showCancelButton: true,
            cancelButtonText: true,
            cancelButtonText: 'No',
            confirmButtonText: 'Sí'
            
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                      Swal.showLoading();
                    }
                });
                var selectedRowsData = dispositivos.rows({ selected: true }).data();
                if (selectedRowsData.length === 0) {
                     Swal.close();
                    Swal.fire({
                        title: 'Error',
                        text: 'Para poder enviar notificaciones se debe seleccionar al menos un dispositivo de la lista.',
                        allowOutsideClick: false,
                        showConfirmButton: 'Aceptar',
                     });
                    return;
                }
                var rowDataToSend = selectedRowsData.toArray();
                var formData = new FormData(this);
                
                formData.append('dispositivos-input', JSON.stringify(rowDataToSend));
                $.ajax({
                    type: "POST",
                    url: postDispositivosSend,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.close();
                        Swal.fire({
                            title: 'Atención',
                            text: response.message,
                            allowOutsideClick: false,
                            icon: response.error?'error':'success',
                            confirmButtonColor: "#47bd9a",
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if(!response.error){
                                    $("button[type='reset']").click();
                                }
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            title: 'Error',
                            allowOutsideClick: false,
                            text: 'Ha ocurrido un error al enviar el formulario.'+xhr.responseText,
                            icon: 'error'
                        });
                    }
                });
            } 
        });
        
      
    });
    $('#datatable-dispositivos_wrapper .dt-search').append('<button id="SelectAllOff" type="button" class="btn btn-info btn-rounded waves-effect ms-2" title="Seleccionar todos"><i class="mdi mdi-select-all"> Seleccionar todos</i></button>');
    $('#SelectAllOff').click(function() {
            if ($(this).hasClass('selected')) {
                dispositivos.rows().deselect();
                $(this).html('<i class="mdi mdi-select-all"></i> Seleccionar todos');
                $(this).removeClass('selected');
                $(this).removeClass('btn-secondary').addClass('btn-info');
            } else {
                dispositivos.rows().select();
                $(this).html('<i class="mdi mdi-select-off"></i> Deseleccionar todos');
                $(this).addClass('selected');
                $(this).removeClass('btn-info').addClass('btn-secondary');
            }
    });
   
}

$(document).ready(function(){

   // dispositivos = setDatatableDispositivos();

    
    $('#selectTipoNotification').change(function(){
        var selectedOption = $(this).val();
        $('#sendNotificationfrm .form-control').prop('disabled', true);
        $('#sendNotificationfrm .form-control').prop('required', false);
        $('#title-input').prop('disabled', false);
        $('#subtitle-input').prop('disabled', false);
        $('#title-input').prop('required', true);
        $('#subtitle-input').prop('required', true);
        switch(selectedOption) {
            case '0':
                $('#url-input').prop('disabled', false);
                $('#date-input').prop('disabled', false);
                $('#customFileNotification').prop('disabled', false);

                $('#url-input').prop('required', false);
                $('#date-input').prop('required', false);
                $('#customFileNotification').prop('required', true);
                console.log("Seleccionaste Multimedia");
                customFileNotification
                break;
            case '1':
                $('#ws-input').prop('disabled', false);
                $('#contacto-input').prop('disabled', false);
                $('#customFileNotification').prop('disabled', true);
                $('#selectNivelNotification').prop('disabled', false);
                
                

                $('#ws-input').prop('required', true);
                $('#contacto-input').prop('required', true);
                $('#customFileNotification').prop('required', true);
                console.log("Seleccionaste Vencimiento");
                break;
            case '2':
                $('#mensaje-input').prop('disabled', false);
                $('#mensaje-input').prop('required', true);
                $('#selectNivelNotification').prop('disabled', true);
                console.log("Seleccionaste HTML");
                break;
            case '3':
                console.log("Seleccionaste Texto");
                $('#selectNivelNotification').prop('disabled', true);
                $('#title-input').prop('disabled', true);
                break;
            default:
                // Acciones por defecto o manejo de errores
                break;
        }
    });
});

