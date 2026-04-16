var contentString = '';
var contentFlotaString = '';
var contentFlotaDetalleString = '';
var contentOrdenTrabajoString = '';
var contentMantenimientoString = '';
var currentInfoWindow = null;
var totales = 0;
var markers=[];

var groupColumnAlert = 0;
var orderConfigAlert = [[0, 'asc']];
var groupColumn = 1;
var orderConfig = [[1, 'asc']];
$(document).ready(function() {
   
    
    
    $("#placaGroup").click(function() {
        groupColumn = 1;
        orderConfig = [[1, 'asc']];
        totales = 0;
        $("a[href='#dashboardAF']").show();
        a.ajax.reload();
    });
    
    $("#FechaGroup").click(function() {
        groupColumn = 6; 
        orderConfig = [[6, 'desc']];
        totales = 0;
        $("a[href='#dashboardAF']").show();
        a.ajax.reload();
    });

    $("#TotalesGroup").click(function() {
        groupColumn = 1; 
        orderConfig = [[1, 'asc']];
        totales = 1;
        $("a[href='#dashboardAF']").hide();
        a.ajax.reload();
    });

    $("#placaGroupAlert").click(function() {
        groupColumnAlert = 1;
        orderConfigAlert = [[1, 'desc']];
        alerta.ajax.reload();
    });
    
    $("#alertaGroupAlert").click(function() {
        groupColumnAlert = 0; 
        orderConfigAlert = [[0, 'asc']];
        alerta.ajax.reload();
    });


    $.get('/infowindows', function(data) {
        contentString = data;
    });
    $.get('/perfilusoflota', function(data) {
        contentFlotaString = data;
    });
    $.get('/detalleflota', function(data) {
        contentFlotaDetalleString = data;
    });
    $.get('/detalleOrdenTrabajo', function(data) {
        contentOrdenTrabajoString = data;
    });

    $.get('/detalleAccionesMasivas', function(data) {
        contentAccDetalleString = data;
       // contentOrdenTrabajoString = data;
    });

    $.get('/detalleMantenimiento', function(data) {
        contentMantenimientoString = data;
    });
    
    $("#datatable").DataTable({
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        }
    });

    

    /**CONTROL MAPA */

    flotaAlt=$("#datatable-flota-alt").DataTable({
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "",
            "infoEmpty":      "",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "_MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar: ",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            buttons: {
                copyTitle: 'Copiado al portapapeles',
                copySuccess: {
                  _: '%d filas copiadas',
                  1: '1 fila copiada'
                }
            }
        },
        stripe: false,
        lengthMenu: [[25, 50, 100, 250, 500, -1], [25, 50, 100, 250, 500,'Todos']],
        drawCallback: function() {
           $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        ajax: {
            url: "/getControlFlotaTableAlt",
            type:"POST",
            cache: true,
            async: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                console.log(d);
                
            },
        },
        scrollY: "100%", 
        scrollCollapse: true,
        /*processing: true,
        paging: true,
        lengthChange: true,*/
        //paging: false, 
        "columns": [
                { 'data': 'Vehiculo' },
                { 'data': 'ESIgn' },
                { 'data': 'Chasis' },
                { 'data': 'Motor' },
                
        ],
        
       
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        console.log('work...!!!');
        initMapFlota(json);
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    /**CONTROL FLOTA */
    var excludedColumns2 = [2, 3, 4, 6, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28, 36, 44, 45, 46, 47, 51, 52, 53, 55];
    var flota=$("#datatable-flota").DataTable({
        serverSide: true,
        lengthMenu: [[20, 50, 100, 250 ,500, 999999], [20, 50, 100, 250 ,500, 'Todos']],
        dom: "<'row'<'col-6'B><'col-6'lf>>" + //agrega un row con dos columnas, col-6 para cada una
         "<'row'<'col-12'rt>>" + //agrega un row con una sola columna
         "<'row'<'col-6'i><'col-6'p>>",
        columnDefs: [
            {
                "targets": [13], 
                "type": 'num'
            },
            {
                "targets": excludedColumns2,
                "visible": false,
                "className": "not-export-col"
            },
            {
                "targets": [1, 5, 7, 11, 12, 14, 19, 20, 29, 30, 31, 32, 33, 34, 35, 37, 38, 39, 40, 41, 42, 43, 48, 49, 50, 54, 57, 58, 59, 60, 61],
                "visible": false,
                
            },
        ],
        autoWidth: false,
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar',
                exportOptions: {
                    columns: ':not(:eq(0), :eq(4), :eq(6), :eq(8), :eq(10), :eq(18), :eq(21), :eq(22), :eq(47), :eq(48), :eq(51), :eq(52), :eq(53), :eq(54), :eq(56), :eq(62)):not(.not-export-col)',
                    exportHiddenColumns: true
                },
            },
            {
                extend: 'excel',
                text: 'Excel',
                exportOptions: {
                    columns: ':not(:eq(0), :eq(4), :eq(6), :eq(8), :eq(10), :eq(18), :eq(21), :eq(22), :eq(47), :eq(48), :eq(51), :eq(52), :eq(53), :eq(54), :eq(56), :eq(62)):not(.not-export-col)',
                    exportHiddenColumns: true
                },
            },
            {
                extend: 'colvis',
                text: 'Mostrar/ocultar columnas',
                columns: ':not(:eq(1), :eq(4), :eq(5), :eq(6), :eq(7), :eq(11), :eq(12), :eq(14), :eq(18), :eq(19), :eq(20), :eq(21), :eq(22), :eq(29), :eq(30), :eq(31), :eq(32), :eq(33), :eq(34), :eq(35), :eq(37), :eq(38), :eq(39), :eq(40), :eq(41), :eq(42), :eq(43), :eq(47), :eq(48), :eq(49), :eq(50), :eq(51), :eq(52), :eq(53), :eq(54), :eq(62))',
                collectionLayout: 'fixed two-column',
                buttonText: 'Columnas <i class="fa fa-angle-down"></i>',
               
            }
        ],
        stateSave: true,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "",
            "infoEmpty":      "",
            "infoFiltered":   "",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            buttons: {
                copyTitle: 'Copiado al portapapeles',
                copySuccess: {
                  _: '%d filas copiadas',
                  1: '1 fila copiada'
                }
            }
        },
        drawCallback: function() {
           $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        deferRender: true, 
        initComplete: function () {
            
        },
        ajax: {
            url: "/getControlFlotaTable",
            type:"POST",
            cache: true,
            async: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.start = d.start;
                d.length = d.length;
                d.searchValue = d.search.value;
                d.orderByColumn = d.order[0].column;
                d.orderDir = d.order[0].dir;
            },
            
        },
        processing: true,
        "columns": [
                { 'data': 'Vehiculo' },
                { 'data': 'Alias' },
                { 'data': 'Chasis' },
                { 'data': 'Motor' },
                { 'data': 'IdMar' },
                { 'data': 'Marca' },
                { 'data': 'IdMod' },
                { 'data': 'Model' },
                { 'data': 'ESIgn' },
                { 'data': 'Propi' },
                { 'data': 'DRep' },
                { 'data': 'UltRep' },
                { 'data': 'DSRep' },
                { 
                    'data': 'Kms', 
                    'type': 'numeric'
                },
                { 'data': 'EIgn' },

                { 'data': 'Lat' },
                { 'data': 'Lon' },
                { 'data': 'TVeh' },
                { 'data': 'Vid' },

                { 'data': 'EVeh' },
                { 'data': 'EOpe' },
                { 'data': 'IdPro' },
                { 'data': 'IdTD' },
                { 'data': 'PCont' },

                { 'data': 'FCob' },
                { 'data': 'ECom' },
                { 'data': 'TCon' },
                { 'data': 'TCon2' },
                { 'data': 'Mail' },

                { 'data': 'PKRD1' },
                { 'data': 'PKRD2' },
                { 'data': 'PKRD3' },
                { 'data': 'PKRD4' },
                { 'data': 'PKRD5' },
                { 'data': 'PKRD6' },
                { 'data': 'PKRD7' },
                { 'data': 'PKRD' },

                { 'data': 'PHUD1' },
                { 'data': 'PHUD2' },
                { 'data': 'PHUD3' },
                { 'data': 'PHUD4' },
                { 'data': 'PHUD5' },
                { 'data': 'PHUD6' },
                { 'data': 'PHUD7' },
                { 'data': 'PHUD' },

                { 'data': 'PUSO' },
                { 'data': 'PVA' },
                { 'data': 'IdAct' },
                { 'data': 'TPlan' },
                
                { 'data': 'PRM' },
                { 'data': 'DRM' },
                { 'data': 'ISP' },
                { 'data': 'ISD' },
                { 'data': 'Plat' },
                { 'data': 'CPM' },
                { 'data': 'PM' },
                { 'data': 'MTTO' },
                { 'data': 'Ciu' },
                { 'data': 'Imei' },
                { 'data': 'FInst' },
                { 'data': 'SInst' },
                { 'data': 'Opera' },
               
                { 'data': 'BOTONES', 'className': 'text-right-alert' },
        ],
        
       
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        //console.log('work...!!!');
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    flota.on('column-visibility.dt', function(e, settings, columnIdx, state) {
        var col = flota.column(columnIdx);
        var header = $(col.header());

        var columnGroupsToProcess = [
            [0, [1, 5, 7]],
            [8, [14, 19, 20]],
            [10, [11, 12]],
            [36, [29, 30, 31, 32, 33, 34, 35]],
            [44, [37, 38, 39, 40, 41, 42, 43]],
            [56, [49, 50]]
        ];
    
        if (state) {
            header.removeClass('not-export-col');
        } else {
            header.addClass('not-export-col');
        }
    
        columnGroupsToProcess.forEach(function(group) {
            var triggerIdx = group[0];
            var columns = group[1];
    
            if (columnIdx === triggerIdx) {
                columns.forEach(function(colIdx) {
                    $(flota.column(colIdx).header()).toggleClass('not-export-col', !state);
                });
            }
        });
        
    });

    $(document).on('click', '#datatable-flota tbody .perfiluso', function() {
        var id = $(this).attr('id');
        id = id.replace('perfil_', '');
        data=flota.row(id).data();
        $('.bs-flota-modal-center .modal-content').html(getContentHtmlFlota(data));
        var serie=[data.PKRD1,data.PKRD2,data.PKRD3,data.PKRD4,data.PKRD5,data.PKRD6,data.PKRD7];
        generateRadialFlota(serie,"#donut-chart-report-flota");
        var serie2=[data.PHUD1,data.PHUD2,data.PHUD3,data.PHUD4,data.PHUD5,data.PHUD6,data.PHUD7];
        generateRadialFlota(serie2,"#donut-chart-report-flota-horas");
        generateBarChartFlota(serie,serie2);
    });

    $(document).on('click', '#datatable-flota tbody .details', function() {
        var id = $(this).attr('id');
        id = id.replace('details_', '');
        data=flota.row(id).data();
        var htmlnew=getContentHtmlFlotaDetails(data);
       console.log(data);
        
        
        $('.bs-flota-details-modal-xl .modal-content').html(htmlnew);
        consultarControlFlotaComandos(userGF, passGF, data.IdAct, data.Vid);
        initMapFlotaInd(parseFloat(data.Lat), parseFloat(data.Lon));
      
    });

    /**SITIOS FRECUENTES*/    

    b=$("#datatable-sitios").DataTable({
        lengthChange: false,
        searching: false,
        info: false,
        paging: false,
        responsive: true,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        scrollY: '170px',
        buttons: {
            copyTitle: 'Copiado al portapapeles',
            copySuccess: {
              _: '%d filas copiadas',
              1: '1 fila copiada'
            }
        },
        
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Excel',
                /*exportOptions: {
                    columns: ':visible'
                }*/
            },
            {
                extend: 'pdf',
                text: 'PDF',
                /*exportOptions: {
                    columns: ':visible'
                }*/
            },
            /*{
                extend: 'colvis',
                text: 'Mostrar/ocultar columnas',
                collectionLayout: 'fixed two-column',
                showAll: 'Todos',
                colVisSelectAll: true
            }*/
           
        ],
        columnDefs: [
            {
              "targets": [0, 1],
              "visible": false,
              "search": false
            },
            {
              "className": "descripcion-column",
              "targets": [2]
            }
        ],
        "order": [[ 3, "desc" ]],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        rowCallback: function(row, data) {
            $(row).on('click', function() {
                var latLng = new google.maps.LatLng(data.Latitud, data.Longitud);
                map.setCenter(latLng);
                if ($(row).hasClass('selected')) {
                    $(row).addClass('highlight');
                } else {
                    $(row).removeClass('highlight');
                }
             });
        },
        ajax: {
            url: "/getSitiosFrecTable",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val(),
                d.vehiculo=$('#vehiculo').val()
            },
        },
        processing: true,
        "columns": [
            { data: 'Latitud' },
            { data: 'Longitud' },
            { data: 'SitioPermanencia' },
            { data: 'Frecuencia' },
            { data: 'TiempoPermanenciaFormato' },
            { data: 'FechaHoraDesde' },
            { data: 'FechaHoraHasta' },
        ],
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        
        var myLatLng =null;
        var icono = null;
       
        var bounds = new google.maps.LatLngBounds();
        $.each(json.data, function(index, item) {
            var tamanoMarcador=index==0?50:(index>0 && index<3?40:(index>=3 && index<5 ?30:20));
            icono = {
                url: "https://res.24hm.net/General/images/icon_map.png",
                scaledSize: new google.maps.Size(tamanoMarcador, tamanoMarcador),
                origin: new google.maps.Point(0, 0),
                //anchor: new google.maps.Point(tamanoMarcador/2, tamanoMarcador/2)
            };
            myLatLng = new google.maps.LatLng(item.Latitud, item.Longitud);
           
            markers['marker_' + index] = new google.maps.Marker({
                position: myLatLng,
                icon: icono,
                map: map
            });
            markers['marker_' + index].setMap(map);
            bounds.extend(markers['marker_' + index].getPosition());
            //addInfoWindow(markers['marker_' + index], 'Contenido del info window ' + index);
            infowindowselectedortable(index,false)
        });
        map.fitBounds(bounds);
       
        
    })   
    // If I do something to make the script error-out, it never goes inside of here. It always goes inside of the ".on('xhr.dt'...)". I want to handle success and error separately.
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

   
    
    $('#datatable-sitios tbody').on('click', 'tr', function () {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $('#datatable-sitios tr').removeClass('selected');
            $(this).addClass('selected');
        }
        var rowIndex = b.row(this).index();
        infowindowselectedortable(rowIndex,true)
    });

    
    
});

function round2dec(numero, decimales) {
    numeroRegexp = new RegExp('\\d\\.(\\d){' + decimales + ',}');   // Expresion regular para numeros con un cierto numero de decimales o mas
    if (numeroRegexp.test(numero)) {         // Ya que el numero tiene el numero de decimales requeridos o mas, se realiza el redondeo
        return Number(numero.toFixed(decimales));
    } else {
        return Number(numero.toFixed(decimales)) === 0 ? 0 : numero;  // En valores muy bajos, se comprueba si el numero es 0 (con el redondeo deseado), si no lo es se devuelve el numero otra vez.
    }
}

function infowindowselectedortable(rowIndex, datatable) {

    
    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });
    var marker = markers['marker_' + rowIndex];
    if(datatable){
        var fila = b.row(rowIndex).data();
        infowindow.setContent(getContentHtml(fila));
        closeinfowindows(infowindow);
        infowindow.open(map, marker);
    } 
    else{
        marker.addListener('click', function() {
            var fila = b.row(rowIndex).data();
            infowindow.setContent(getContentHtml(fila));
            closeinfowindows(infowindow)
            infowindow.open(map, marker);
            b.rows().deselect();
            b.row(rowIndex).select();
            var selectedRow = $('#datatable-sitios').DataTable().rows('.selected');
            var position = selectedRow.nodes().to$().position().top;
            $('#datatable-sitios').parent().scrollTop(position);
        });
    }
    
}

function closeinfowindows(infowindow) {
    if (currentInfoWindow) {
        currentInfoWindow.close();
    }
    currentInfoWindow = infowindow;
}

function getContentHtml(fila) {
    var newhtml=contentString.replace("*DIR*", fila.SitioPermanencia);
    console.log(fila);
    newhtml=newhtml.replace("*UVH*", fila.FechaHoraHasta);
    newhtml=newhtml.replace("*FRE*", fila.Frecuencia);
    newhtml=newhtml.replace("*UVD*", fila.FechaHoraDesde);
    newhtml=newhtml.replace("*TTF*", fila.TiempoPermanenciaFormato);
    newhtml=newhtml.replace("*LAT*", fila.Latitud);
    newhtml=newhtml.replace("*LON*", fila.Longitud);
    return newhtml;
}

function getContentHtmlFlota(fila) {
    var newhtml=contentFlotaString.replace("*DIR*", fila.SitioPermanencia);
    newhtml=newhtml.replace("*ALIAS*", fila.Alias);
    newhtml=newhtml.replace("*MARCA*", fila.Marca);
    newhtml=newhtml.replace("*PROPIETARIO*", fila.Propi);
    newhtml=newhtml.replace("*MARCA*", fila.Marca);
    newhtml=newhtml.replace("*PERFIL*", fila.PUSO);
    if (fila.PVA == 'ND') {
        newhtml = newhtml.replace("*VA*", fila.PVA);
    } else {
        newhtml = newhtml.replace("*VA*", fila.PVA + ' v');
    }
    newhtml=newhtml.replace("*KMS*", fila.PKRD);
    newhtml=newhtml.replace("*HUSO*", fila.PHUD);
    
    return newhtml;
}

function getContentHtmlMantenimiento(fila) {
    var newhtml=contentOrdenTrabajoString.replace("*FFIN*", fila.FechaFin);
    newhtml=newhtml.replace("*IDCODE*", fila.OrdenTrabajo);
    newhtml=newhtml.replace("*IDORD*", fila.OrdenTrabajo);
    newhtml=newhtml.replace("*HFIN*", fila.HoraFin);
    newhtml=newhtml.replace("*FINI*", fila.FechaInicio);
    newhtml=newhtml.replace("*HINI*", fila.HoraInicio);
    newhtml=newhtml.replace("*ODO*", fila.Odometro);
    newhtml=newhtml.replace("*REST*", fila.ResponsableTrabajo);
    newhtml=newhtml.replace("*RESM*", fila.ResponsableSeguimiento);
    newhtml=newhtml.replace("*HTRA*", fila.HorasTrabajo);
    newhtml=newhtml.replace("*PREC*", fila.PrecioTrabajo);
    newhtml=newhtml.replace("*DETALLE*", fila.Observaciones);
    
    newhtml=newhtml.replace("*IDMAR*", fila.IdMarcaOrdenTrabajo);
    newhtml=newhtml.replace("*IDMOD*", fila.IdModeloOrdenTrabajo);
    newhtml=newhtml.replace("*PPLAN*", fila.PorPlanMantenimiento);
    newhtml=newhtml.replace("*IDUPUSU*", fila.IdUnidadPlanUsuario);

    newhtml=newhtml.replace("*IDUPUSU*", fila.IdUnidadPlanUsuario);
    newhtml=newhtml.replace("*IDPUSU*", fila.IdPlanUsuario);
    newhtml=newhtml.replace("*SECUE*", fila.Secuencia);
    newhtml=newhtml.replace("*IDUSU*", fila.IdUsuario);
;
    
    return newhtml;
}

function getContentHtmlAcc(fila) {
    var newhtml=contentAccDetalleString.replace("*NFILE*", fila.NombreArchivoTransaccional);
    newhtml=newhtml.replace("*IDFILE*", fila.IdArchivoTransaccional);
    newhtml=newhtml.replace("*ALIAS*", fila.NombreArchivoTransaccional);
    
    return newhtml;
}

function getContentHtmlFlotaDetails(fila) {
    console.log(fila)
    comandoname=fila.EOpe=='Desbloqueado'?'Bloquear':'Desbloquear'
    $('#mySmallModalLabel').html(vv(fila.Alias));
    $('#odometro').val(vv(fila.Kms));
    var newhtml=contentFlotaDetalleString.replace("*DIR*", vv(fila.SitioPermanencia));
    newhtml=newhtml.replace("*ALIAS*", vv(fila.Alias));
    newhtml=newhtml.replace("*MARCA*", vv(fila.Marca));
    newhtml=newhtml.replace("*PROPIETARIO*", vv(fila.Propi));
    newhtml=newhtml.replace("*MARCA*", vv(fila.Marca));
    newhtml=newhtml.replace("*MODELO*", vv(fila.Model));
    newhtml=newhtml.replace("*KILOMETRAJE*", vv(Math.round(fila.Kms)));
    newhtml=newhtml.replace("*KILOMETRAJE2*", vv(Math.round(fila.Kms)));
    newhtml=newhtml.replace("*ESTADOVEH*", vv(fila.EIgn));
    newhtml=newhtml.replace("*ULTREP*", vv(fila.UltRep));
    newhtml=newhtml.replace("*DIASREP*", vv(fila.DSRep));


    newhtml=newhtml.replace("*MOTOR*", vv(fila.Motor));
    newhtml=newhtml.replace("*CHASIS*", vv(fila.Chasis));
    newhtml=newhtml.replace("*DRM*", vv(fila.DRM));
    newhtml=newhtml.replace("*PRM*", vv(fila.PRM));

    newhtml=newhtml.replace("*CIU*", vv(fila.Ciu));
    newhtml=newhtml.replace("*IMEI*", vv(fila.Imei));
    newhtml=newhtml.replace("*FINS*", vv(fila.FInst));
    newhtml=newhtml.replace("*SINS*", vvlist(fila.SInst));
    newhtml=newhtml.replace("*OPERA*", vvlist(fila.Opera));



    newhtml=newhtml.replace("*PKD*", vv(fila.PKRD));
    newhtml=newhtml.replace("*PHD*", vv(fila.PHUD));
    newhtml=newhtml.replace("*COORD*", vv('('+parseFloat(fila.Lat).toFixed(7)+','+parseFloat(fila.Lon).toFixed(7)+')'+' <a href="https://www.google.com/maps?q='+fila.Lat+','+fila.Lon+'" target="_blank"><button type="button" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-map-marker-radius align-middle"></i></button></a>'));

    newhtml=newhtml.replace("*PRODUCTO*", vv(fila.PCont));
    newhtml=newhtml.replace("*FINCOBERTURA*", vv(vv(fila.FCob)));
    newhtml=newhtml.replace("*TELEFONO1*", vv(fila.TCon));
    newhtml=newhtml.replace("*TELEFONO2*", vv(fila.TCon2));
    newhtml=newhtml.replace("*CORREO*", vv(fila.Mail));
    newhtml = newhtml.replace("*VARLAT*", vv(fila.Lat));
    newhtml = newhtml.replace("*VARLON*", vv(fila.Lon));
    newhtml = newhtml.replace("*CODIGOBLOQUEO*", vv(fila.IdAct+','+fila.Vid+','+(fila.EOpe=='Desbloqueado'?2:3)));
    newhtml = newhtml.replace("*NAMEBLOQUEO*", vv(fila.EOpe=='Desbloqueado'?'Bloquear':'Desbloquear'));
    newhtml = newhtml.replace("*VID*", vv(fila.Vid));
    return newhtml;
}

function consultarControlFlotaComandos(usuario, clave, idActivo, idDispositivo) {
    var url = "/getControlFlotaComandos";
    var parametros = {
        usuario: usuario,
        clave: clave,
        idActivo: idActivo,
        idDispositivo: idDispositivo
    };
    return $.get(url, parametros).then(function(data) {
        try {
            var jsonObject = JSON.parse(data);
           
            var botones = "";
            jsonObject.Comandos.forEach(function(elemento) {
                console.log(elemento);
                botones += '<div class="col-4 p-1"><button type="button" onclick="comandoBloqueo('+ idActivo+','+idDispositivo+','+ elemento.IdCom +',\''+ elemento.NCom + '\')" class="btn btn-primary waves-effect waves-primary details h-100 w-100" ><i class="mdi mdi-view-grid-outline align-middle me-2 "></i>' + elemento.NCom + '</button></div>';
            });

            botones += '<div class="col-4 text-center p-1"><button type="button" onclick="showChangeOdo()" id="btn_odometro" class="btn btn-primary waves-effect waves-primary details h-100 w-100" ><i class="mdi mdi-view-grid-outline align-middle me-2 "></i>Cambiar Odometro</button></div>';
            $("#contenedor-botones").html(botones);
        } catch (error) {
            // En caso de error, no hagas nada o maneja el error según tus necesidades
        }
    });
}

function vv(variable) {
if (variable === '' || variable === null || typeof variable === 'undefined') {
    return '--';
} else {
    return variable;
}
}

function vvlist(variable) {
if (!variable || typeof variable !== 'string') {
    return '--';
    }
    
    const items = variable.split('<br/>');
    if (items.length === 0) {
    return '--';
    }
    
    const listItems = items.map(item => `<li>${item}</li>`).join('');
    const ul = `<ul>${listItems}</ul>`;
    
    return ul;
}

function startDataOrdenTrabajo(data, id){
$(document).ready(function() {
    $('#formOrdenTrabajo').on('submit',function(e){
        e.preventDefault();
        var formData = $(this).serialize();
        if($('#idEstadoOrden option:selected').text()=='COMPLETADA' && !$('#tipoTrabajo').val() && !$('#odometro').val() && !$('#responsableTrabajo').val() && !$('#horasTrabajo').val() && !$('#precioTrabajo').val() && !$('#observaciones').val()){
            Swal.fire({
                title: 'Atención',
                text: 'Se sugiere completar los datos del espacio Detalle de Trabajo',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: "#47bd9a",
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    submitOrdenTrabajo(data, id,formData);
                } 
            });
        }else{
            submitOrdenTrabajo(data, id, formData);
        }
    });
});
}

function submitOrdenTrabajo(data, id,formData){
    
    var codigosActividades = $('#codigosActividades').val();
    if (codigosActividades && codigosActividades.length > 0) {
        var actividades = codigosActividades.join(',');
        formData += '&codigosActividades=' + actividades;
    }
    var codigosActivos = $('#multiple_vehiculos2').val();
    if (codigosActivos && codigosActivos.length > 0) {
        var veh = codigosActivos.join(',');
        formData += '&codigosActivos=' + veh;
    }
    formData+='&tipoMantenimiento='+data.PorPlanMantenimiento;
    Swal.fire({
        title: 'Procesando',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
    $.ajax({
        url: '/reportes/modificarOrdenTrabajo', 
        type: 'POST', 
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        "data": formData,
        success: function(response) {
            var responseObject = JSON.parse(response);
            Swal.close();
            Swal.fire({
                title: 'Atención',
                allowOutsideClick: false,
                text: responseObject.Mensaje,
                icon: responseObject.Error?'error':'success',
                confirmButtonColor: "#47bd9a",
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!$.fn.DataTable.isDataTable('#datatable-mantenimiento')) {
                        mantenimientoTable();
                    } else {
                        c.ajax.reload();
                    }
                    !responseObject.Error?$('.bs-ordentrabajo-details-modal-xl .btn-close').click():null;
                }
            });

                    
            
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error',
                allowOutsideClick: false,
                text: 'Ha ocurrido un error al enviar el formulario.',
                icon: 'error'
            });
            console.log('Error en la petición Ajax');
        }
    });
}

function comandosTable(){
    comandosT=$("#datatable-comandos").DataTable({
        deferRender: true,
        lengthChange: true,
        searching: true,
        lengthMenu: [ [25, 50, 100, -1], ["25", "50", "100", "Todos"] ],
        info: false,
        paging: true,
        dom: //"<'row'<'col-md-6'l><'col-md-6'f>>" +
             "<'row'<'col-md-6'B><'col-md-6'lf>>" +

             "<'row'<'col-md-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
        },
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar'
            },
            {
                extend: 'excel',
                text: 'Excel',
            },
            {
                extend: 'print',
                text: 'Imprimir',
            }
           
        ],
        
        "order": [[ 1, "desc" ]],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        ajax: {
            url: "/reportes/getReporteComandos",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val(),
                d.multiple_vehiculos=$('#multiple_vehiculos_alt_case .ms-choice span').html()=='TODOS'?null:$('#multiple_vehiculos_alt').val(),
                d.multiple_grupos=$('#multiple_grupos_case .ms-choice span').html()=='TODOS'?null:obtenerVehArrayByGruposub($('#multiple_grupos').val())
            },
        },
        processing: false,
        "columns": [
            { data: 'Alias' },
            { data: 'Fecha' },
            { data: 'Vid' },
            { data: 'Usuario' },
            { data: 'NombreAplicacion' },
            { data: 'NombreComando'},
            { data: 'Celular'},
            { data: 'Ip'},
            { data: 'Observaciones' },
            
        ],
        
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });
}

function transmisionTable(){
    transmision=$("#datatable-transmision").DataTable({
        lengthChange: true,
        searching: true,
        lengthMenu: [ [25, 50, 100, -1], ["25", "50", "100", "Todos"] ],
        info: false,
        paging: true,
        dom: //"<'row'<'col-md-6'l><'col-md-6'f>>" +
             "<'row'<'col-md-6'B><'col-md-6'lf>>" +

             "<'row'<'col-md-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            
        },
        columnDefs: [
            { targets: 2, visible: false },
            { targets: 3, visible: false },
            { targets: 4, visible: false },/*
            { targets: 5, visible: false }*/

        ],
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar'
            },
            {
                extend: 'excel',
                text: 'Excel',
                exportOptions: {
                    columns: ':not(:eq(0))',
                    exportHiddenColumns: true
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                exportOptions: {
                    columns: ':not(:eq(0))',
                }
            }
           
        ],
        
        "order": [[ 1, "desc" ]],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        ajax: {
            url: "/reportes/getReporteTransmision",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val(),
                d.multiple_vehiculos=$('#multiple_vehiculos_alt_case .ms-choice span').html()=='TODOS'?null:$('#multiple_vehiculos_alt').val(),
                d.multiple_grupos=$('#multiple_grupos_case .ms-choice span').html()=='TODOS'?null:obtenerVehArrayByGruposub($('#multiple_grupos').val()),
                
              
                //d.totalOptions = $('#multiple_vehiculos option').length
                d.totalOptions = $('#multiple_vehiculos_alt_case .ms-choice span').html()
            },
        },
        processing: true,
        "columns": [
            //{ data: 'IdEnt' },
            //{ data: 'IdAct' },
           
            { data: 'Vehiculo' },
            
            //{ data: 'Fecha' },
            { data: 'Vid' },
            { data: 'Alias' },
            { data: 'Marca' },
            { data: 'Modelo' },
            
            //{ data: 'Color' },
            //{ data: 'Anio' },
            //{ data: 'CSHunter' },
            { data: 'Reportes' },
            { data: 'Trayectos' },
            { data: 'Detenciones' },
            { data: 'Permanencias' },
            { data: 'Producto' },
            { data: 'Motor' },
            { data: 'Chasis' },
            //{ data: 'Grupos' },
            //{ data: 'SubGrupos' }
        ],
        
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });
}

function alertaTable(){
    alerta=$("#datatable-alerta").DataTable({
        lengthChange: true,
        lengthMenu: [ [25, 50, 100, -1], ["25", "50", "100", "Todos"] ],
        searching: true,
        info: false,
        paging: true,
        dom: //"<'row'<'col-md-6'l><'col-md-6'f>>" +
             "<'row'<'col-md-6'B><'col-md-6'lf>>" +

             "<'row'<'col-md-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            
        },
       /* columnDefs: [
            { targets: 1, visible: false }
        ],*/
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar'
            },
            {
                extend: 'excel',
                text: 'Excel',
                /*exportOptions: {
                    columns: ':not(:eq(0))',
                    exportHiddenColumns: true
                }*/
            },
            {
                extend: 'print',
                text: 'Imprimir',
               /* exportOptions: {
                    columns: ':not(:eq(0))',
                }*/
            }
           
        ],
        
        order: orderConfigAlert,
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
            
            var api = this.api();
            var rows = api.rows({ page: 'current' }).nodes();
            var last = null;
        
            api.column(groupColumnAlert, { page: 'current' })
                .data()
                .each(function (group, i) {
                    if (last !== group) {
                        // Crear una fila de grupo para cada cambio en el valor de la columna 0
                        $(rows).eq(i).before(
                            '<tr class="group-header">' +
                            '<td colspan="' + api.columns(':visible').count() + '" class="group-tdheader font-size-15 mb-0" style="font-weight: bolder;">' +
                            '<i class="fas fa-chevron-circle-down toggle-icon"></i>' +
                            (groupColumnAlert==1?'Placa: ':'Alerta: ') + group +
                            '</td>' +
                            '</tr>'
                        );
                        last = group;
                    }
                });
        },
        
        ajax: {
            url: "/reportes/getReporteAlertas",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val(),
                d.multiple_vehiculos=$('#multiple_vehiculos_alt_case .ms-choice span').html()=='TODOS'?null:$('#multiple_vehiculos_alt').val(),
                d.multiple_grupos=$('#multiple_grupos_case .ms-choice span').html()=='TODOS'?null:obtenerVehArrayByGruposub($('#multiple_grupos').val()),
                d.multiple_alertas=$('#multiple_alertas_case .ms-choice span').html()=='TODOS'?null:$('#multiple_alertas').val(),
                //d.totalOptions = $('#multiple_vehiculos option').length
                d.totalOptions = $('#multiple_vehiculos_alt_case .ms-choice span').html()
            },
            beforeSend: function() {
                
            },
            complete: function() {
                
               
            }
        },
        processing: true,
        "columns": [
            { data: 'Ale' },
            { data: 'Alias' },
            { data: 'Fecha' },
            { data: 'Calle' },
            { data: 'Punto' },
            { data: 'Zonas'},
            { data: 'Coord'},
            { data: 'Vel'},
            { data: 'Odo' },
            { data: 'EGps' },

        
        ],
        
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        alerta.order(orderConfigAlert).draw();
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    $('#datatable-alerta tbody').on('click', 'tr.group-header', function() {
        var current_row = $(this);
        var next_row = current_row.next();
        while (next_row.length && !next_row.hasClass('group-header')) {
          next_row.toggle();
          next_row = next_row.next();
        }
        current_row.find('.toggle-icon').toggleClass('hide');
      });
}

function mantenimientoTable(){
    c=$("#datatable-mantenimiento").DataTable({
        lengthChange: true,
        searching: true,
        info: false,
        paging: true,
        dom: //"<'row'<'col-md-6'l><'col-md-6'f>>" +
             "<'row'<'col-md-6'B><'col-md-6'lf>>" +

             "<'row'<'col-md-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            
        },
        columnDefs: [
            { targets: 1, visible: false }
        ],
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar'
            },
            {
                extend: 'excel',
                text: 'Excel',
                exportOptions: {
                    columns: ':not(:eq(0)):not(:eq(9))',
                    exportHiddenColumns: true
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                exportOptions: {
                    columns: ':not(:eq(0)):not(:eq(9))',
                }
            }
           
        ],
        
        "order": [[ 1, "desc" ]],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        ajax: {
            url: "/reportes/getOrdenTrabajoTable",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.multiple_vehiculos=$('#multiple_vehiculos_case .ms-choice span').html()=='TODOS'?null:$('#multiple_vehiculos').val(),
                d.multiple_grupos=$('#multiple_grupos_case .ms-choice span').html()=='TODOS'?null:$('#multiple_grupos').val(),
                d.marca_modelo=$('#marca_modelo').val(),
                d.actividades=$('#actividades').val(),
                //d.totalOptions = $('#multiple_vehiculos option').length
                d.totalOptions = $('#multiple_vehiculos_case .ms-choice span').html()
            },
        },
        processing: true,
        "columns": [
            { data: 'Vehiculo' },
            { data: 'VehiculoAlias' },
            { data: 'OrdenTrabajo' },
            { data: 'ESIgn' },
            { data: 'Actividades' , className: 'wrap'},
            { data: 'EstadoMant' },
            { data: 'FechaProgramada', className: 'text-center' },
            { data: 'FechaRealizado', className: 'text-center' },
            { data: 'Cumplimiento', className: 'text-center' },
            { data: 'EstadoOrden' },
            { data: 'boton' },
        ],
        
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        if (json && json.KPIs && json.KPIs.length > 0) {
            $("#countVeh").html(''+json.KPIs[0]);
            $("#reccKms").html(''+json.KPIs[1]);
            $("#velPro").html(''+json.KPIs[2]);
            $("#calif").html(''+json.KPIs[3]);
        }else{
            $("#countVeh").text('--');
            $("#reccKms").text('--');
            $("#velPro").text('--');
            $("#calif").text('--');
        }
       
        
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    $(document).on('click', '#datatable-mantenimiento tbody .mantenimiento', function() {
        var id = $(this).attr('id');
        id = id.replace('mantenimiento_', '');
        data=c.row(id).data();
        //console.log(data);
        $('.bs-ordentrabajo-details-modal-xl .modal-content').html(getContentHtmlMantenimiento(data));
        $('select#tipoTrabajo').val(''+data.TipoTrabajo);
        $('select#tipoMantenimiento').val(''+data.PorPlanMantenimiento);
        $.each(listEOrd, function(index, EOrden) {
            if(data.IdEstadoOrden==EOrden.IdEstadoOrden){
                $('select#idEstadoOrden').append('<option value="' + EOrden.IdEstadoOrden + '" selected>' + EOrden.DescripcionEstadoOrden.toUpperCase() + '</option>');
            }else{
                $('select#idEstadoOrden').append('<option value="' + EOrden.IdEstadoOrden + '">' + EOrden.DescripcionEstadoOrden.toUpperCase() + '</option>');
            }
        });
        if(data.IdEstadoOrden=="7"){
            $('#htrabajol').html('Horas Trabajo Total');
            $('#preciol').html('Precio Total');
        }
        var codigosActividadesArr = data.CodigosActividades.split(',').filter(Boolean);
        $('#codigosActividades').empty();
        $.each(listAct, function(index, act) {
            var selected = (codigosActividadesArr.includes(act.IdAct)) ? 'selected' : '';
            $('select#codigosActividades').append('<option value="' + act.IdAct + '" ' + selected + '>' + act.DAct.toUpperCase() + '</option>');
        });
        $('#codigosActividades').multipleSelect('refresh');

        var codigosActivosArr = data.CodigosActivos.split(',').filter(Boolean);
        $('#multiple_vehiculos2').empty();
        $.each(listVeh, function(index, vehiculo) {
            var selected = (codigosActivosArr.includes(vehiculo.IdAct)) ? 'selected' : '';
            $('select#multiple_vehiculos2').append('<option value="' + vehiculo.IdAct + '" ' + selected + '>' + vehiculo.Alias + ' (' + vehiculo.Marca + ' - ' + vehiculo.Model + ')' + '</option>');
        });
        $('#multiple_vehiculos2').multipleSelect('refresh');

        if(data.PorPlanMantenimiento == 1) {
            $('#multiple_vehiculos2').prop('disabled', true);
        }
        $('#tipoMantenimiento').prop('disabled', true);
        startDataOrdenTrabajo(data);
    });
}

function accMasivasTable(){
    acc=$("#datatable-reporteacc").DataTable({
        lengthChange: true,
        searching: true,
        info: false,
        paging: true,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
        },
        "order": [[ 2, "desc" ]],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        },
        ajax: {
            url: "/reportes/getConsultaLogsOperacionesMasivas",
            type:"POST",
            cache: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val()
                d.estado=$('#estado').val()
            },
        },
        processing: true,
        "columns": [
            { data: 'IdArchivoTransaccional' },
            { data: 'NombreArchivoTransaccional' },
            { data: 'FechaHoraTransaccional' },
            { data: 'Estado'},
            { data: 'Observaciones' },
            { data: 'Boton' },

        ],
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
    })   
    .on('error.dt', function ( e, settings, techNote, message ) {
        console.log(message);
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    $(document).on('click', '#datatable-reporteacc tbody .detalleAcc', function() {
        var id = $(this).attr('id');
        id = id.replace('detalle_', '');
        data=acc.row(id).data();
        console.log(data);
        $('.bs-acc-modal-xl .modal-content').html(getContentHtmlAcc(data));
        startDataACCMASS(data.NombreArchivoTransaccional,data.IdArchivoTransaccional);
    });
}

function startDataACCMASS(name,id){
    $(document).ready(function() {
        accdet=$("#datatable-reporteaccdet").DataTable({
            lengthChange: true,
            searching: true,
            info: false,
            paging: true,
            language: {
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                },
                "loadingRecords": "&nbsp;",
                "processing": "Cargando...",
                "emptyTable":     "No hay datos disponibles",
                "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
                "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
                "infoFiltered":   "(filtrado desde _MAX_ total registros)",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Buscar:",
                "zeroRecords":    "No existen registros que coincidan",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Último",
                    "next":       "Sig",
                    "previous":   "Prev"
                },
                
            },
            
            
            "order": [[ 2, "desc" ]],
            drawCallback: function() {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
            },
            ajax: {
                url: "/reportes/getConsultaLogsOperacionesMasivasDetalle",
                type:"POST",
                cache: true,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                "data": function ( d ) {
                    d.nombreArchivo= name,
                    d.estado= $('#estadoDet').val(),
                    d.idArchivo=id
                },
            },
            processing: true,
            "columns": [
                { data: 'Placa' },
                { data: 'Chasis' },
                { data: 'Motor' },
                { data: 'IdOperacion'},
                { data: 'Odometro' },
                { data: 'FechaHoraModificacion' },
                { data: 'Observacion' },
    
            ],
            
        }).on('xhr.dt', function ( e, settings, json, xhr ) {
            
           
            
        })   
        .on('error.dt', function ( e, settings, techNote, message ) {
            console.log(message);
            console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
        });
    
        $('#filterAccionesMasivasDetalle').on('submit',function(e){
            e.preventDefault();
            accdet.ajax.reload();
        });
        
    });
}

function actividadFlotaTable(){
    /**ACTIVIDAD FLOTA */
    var loadingAlert = Swal.mixin({
        title: 'Procesando....',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
    var excludedColumns = [4, 5, 10, 11, 15, 16, 17, 24, 25, 26, 27, 28, 29, 33, 34, 36];
    var excludedColumns = [4, 5, 10, 11, 15, 16, 17, 24, 25, 26, 27, 28, 29, 33, 34, 36];
    a = $("#datatable-buttons").DataTable({
        responsive: true,
        dom: 'Blfrtip',
        stateSave: true,
        lengthMenu: [[20, 50, 100, -1], [20, 50, 100,'Todos']],
        columnDefs: [{
            "targets": excludedColumns,
            "visible": false,
            "className": "not-export-col"
        },{
            "targets": [1, 2, 3, 19, 20, 22, 23],
            "visible": false
        }],
        buttons: [
            {
                extend: 'copy',
                text: 'Copiar'
            },
            {
                extend: 'excel',
                text: 'Excel',
                exportOptions: {
                    //columns: ':not(:eq(0)):not(:eq(15)):not(.not-export-col)',
                    columns: ':not(:eq(0), :eq(18)):not(.not-export-col)',
                    exportHiddenColumns: true
                }
            },
            {
                extend: 'colvis',
                text: 'Mostrar/ocultar columnas',
                columns: ':not(:eq(1), :eq(2), :eq(3), :eq(19), :eq(20), :eq(22), :eq(23))',
                //columns: ':not(:eq(0), :eq(4), :eq(6), :eq(8), :eq(10), :eq(18), :eq(21), :eq(22), :eq(47), :eq(48), :eq(51), :eq(52), :eq(53), :eq(54), :eq(56), :eq(57)):not(.not-export-col)',
                collectionLayout: 'fixed two-column',
                buttonText: 'Columnas <i class="fa fa-angle-down"></i>',
                showAll: 'Todos',
                colVisSelectAll: true
            }
        ],
        
        order: orderConfig,
        
        //ordering: false,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            "loadingRecords": "&nbsp;",
            "processing": "Cargando...",
            "emptyTable":     "No hay datos disponibles",
            "info":           "Mostrando _START_ al _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 al 0 de 0 registros",
            "infoFiltered":   "(filtrado desde _MAX_ total registros)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No existen registros que coincidan",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Sig",
                "previous":   "Prev"
            },
            buttons: {
                copyTitle: 'Copiado al portapapeles',
                copySuccess: {
                  _: '%d filas copiadas',
                  1: '1 fila copiada'
                }
            }
        },
        
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            if(totales==0){
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                var last = api.column(groupColumn, { page: 'current' }).data()[0];
                //console.log(api.column(groupColumn, { page: 'current' }).data());
                var group_distance = 0;
                var group_speed = 0;
                var group_consumo = 0;
                var group_huella = 0;
                var count = 0;
                var colspan = api.columns(':visible').count();
                api.column(groupColumn, { page: 'current' })
                .data()
                .each(function (group, i) {
                    var distance = api.column(9, { page: 'current' }).data()[i];
                    var speed = api.column(10, { page: 'current' }).data()[i];
                    var consumo = api.column(32, { page: 'current' }).data()[i];
                    var huella = api.column(35, { page: 'current' }).data()[i];
                    if (last != group || i==0) {
                        // update last group header
                        if(i>0){
                            if (last !== null) {
                                var lastRowIndex = i - count;
                                var lastRow = $(rows).eq(lastRowIndex).prev();
                                lastRow.find('.distance').text(round2dec(group_distance,1));
                                lastRow.find('.speed').text((group_speed/count).toFixed(1));
                                lastRow.find('.consumo').text((group_consumo/count).toFixed(1));
                                lastRow.find('.huella').text((group_huella/count).toFixed(1));
                                lastRow.find('.count').text(count);
                                group_distance = 0;
                                group_speed = 0;
                                group_consumo = 0;
                                group_huella = 0;
                                count = 0;
                            }
                        }
                        $(rows)
                        .eq(i)
                        .before('<tr class="group-header"><td colspan="'+(colspan)+'" class="group-tdheader font-size-15 mb-0" style="font-weight: bolder;"><i class="fas fa-chevron-circle-down toggle-icon" ></i>'+
                            (groupColumn==1?'Placa: ':'Día: ')+ group + ''+
                            '<div class="alert alert-info fade show font-size-12 mr-1 font-weight-bold" style="padding: 3px 5px !important;float:right;margin-bottom:0px!important;margin-right:5px; cursor:pointer;" title="Número de vehículos" role="alert">'+
                            '<i class="mdi mdi-car-multiple me-2"></i><strong class="count">0</strong>'+
                            '</div>'+
                            '<div class="alert alert-secondary fade show font-size-12 mr-1" style="padding: 3px 5px !important;float:right;margin-bottom:0px!important;margin-right:5px;cursor:pointer;" title="Consumo Promedio" role="alert">'+
                            '<i class="mdi mdi-gas-station me-2"></i>'+
                            '<strong class="consumo">0 Gal/h</strong>'+
                            '</div>'+
                            '<div class="alert alert-secondary fade show font-size-12 mr-1" style="padding: 3px 5px !important;float:right;margin-bottom:0px!important;margin-right:5px;cursor:pointer;" title="Huella Carbono Promedio" role="alert">'+
                            '<i class="mdi mdi-molecule-co2 me-2"></i>'+
                            '<strong class="huella">0 Kgs</strong>'+
                            '</div>'+
                            '<div class="alert alert-secondary fade show font-size-12 mr-1" style="padding: 3px 5px !important;float:right;margin-bottom:0px!important;margin-right:5px;cursor:pointer;" title="Velocidad Promedio" role="alert">'+
                            '<i class="mdi mdi-speedometer me-2"></i>'+
                            '<strong class="speed">0 km/h</strong>'+
                            '</div>'+
                            '<div class="alert alert-secondary fade show font-size-12 mr-1" style="padding: 3px 5px !important;float:right;margin-bottom:0px;margin-right:5px;cursor:pointer;" title="Distancia Recorrida" role="alert">'+
                            '<i class="mdi mdi-map-marker-distance me-2"></i>'+
                            '<strong class="distance">0 Kms<strong>'+
                            '</div>'+
                            '</td>'+
                            '</tr>');
                        last = group;
                        
                    }
                    
                    group_distance += round2dec(distance,1);
                    group_speed += round2dec(speed,1);
                    group_consumo += round2dec(parseFloat(consumo.trim()),1);
                    group_huella += round2dec(parseFloat(huella),1);
                    count++;
                    if (i === api.column(groupColumn, { page: 'current' }).data().length - 1) {
                        var lastRowIndex = i - (count-1);
                        var lastRow = $(rows).eq(lastRowIndex).prev();
                        lastRow.find('.distance').text(round2dec(group_distance,1));
                        lastRow.find('.speed').text((group_speed/count).toFixed(1));
                        lastRow.find('.consumo').text((group_consumo/count).toFixed(1));
                        lastRow.find('.huella').text((group_huella/count).toFixed(1));
                        lastRow.find('.count').text(count);
                    }
                });
            }
        },
        initComplete: function(settings, json) {
            a.order(orderConfig).draw();
        },
       
        ajax: {
            url: globalTable?'/reportes/getAccMasivasTableGlobal':'/reportes/getAccMasivasTable',
            type:"POST",
            cache: true,
            //"dataSrc": "Data",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function ( d ) {
                //d.min=$('#min-input').val(),
                d.fechaIni= $('#dateini-input').val(),
                d.fechaFin=$('#datefin-input').val(),
                d.multiple_vehiculos=$('#multiple_vehiculos_case .ms-choice span').html()=='TODOS'?null:$('#multiple_vehiculos').val(),
                d.global=globalTable,
                d.totales=totales
            },
            /*"success": function(data) {
                $("#countVeh").html(''+data.Graph.KPI[0]);
                $("#reccKms").html(''+data.Graph.KPI[1].toFixed(2));
                $("#velPro").html(''+data.Graph.KPI[2]+' Km/h');
                $("#calif").html(''+data.Graph.KPI[3]);
                //$("#countVeh").hmtl(''+data.Graph.KPI[0]);
            }*/
            beforeSend: function() {
                loadingAlert.fire(); // Mostrar el mensaje de carga antes de la solicitud Ajax
            },
            complete: function() {
                a.order(orderConfig).draw();
                loadingAlert.close(); // Cerrar el mensaje de carga después de recibir la respuesta Ajax
            }
        },
        processing: true,
        "columns": [
            { data: 'Vehiculo' },
            { data: 'Placa' },
            { data: 'Marca' },
            { data: 'Modelo' },
            { data: 'Chasis' },
            { data: 'Motor' },
            { data: 'Dia' },
            { data: 'HoraInicio' },
            { data: 'HoraFin' },
            { data: 'DistanciaTotal' },
            { data: 'VelocidadPromedio' },
            { data: 'VelocidadMaxima' },
            { data: 'tTiempoManejo' },
            { data: 'tTiempoRalenti' },
            { data: 'tTiempoApagado' },
            { data: 'Calificacion' },
            { data: 'ViajesTotal' },
            { data: 'ViajesTotalSD' },
            { data: 'Eventos' },
            { data: 'Frenadas' },
            { data: 'Aceleraciones' },
            { data: 'Excesos' },
            { data: 'Giros' },
            { data: 'Impactos' },
            
            { data: 'Conductor' },
            { data: 'Mayores100' },
            { data: 'Mayores90' },
            { data: 'Mayores80' },
            { data: 'OdoInicial' },
            { data: 'OdoFinal' },
            { data: 'DirInicial' },
            { data: 'DirFinal' },

            { data: 'ConsumoCombustible' },
            //{ data: 'UnidadCombustible' },
            { data: 'ConsumoRalenti' },
            { data: 'ConsumoMovimiento' },
            { data: 'HuellaCarbono' },
            { data: 'ArbolesNecesarios' },

        ],
    }).on('xhr.dt', function ( e, settings, json, xhr ) {
        if (loadingAlert) {
            loadingAlert.close(); 
        }
        if (json){
            if (json.Graph && json.Graph.KPI && json.Graph.KPI.length > 0) {
                $("#countVeh").text(json.Graph.KPI[0]);
                $("#reccKms").text(json.Graph.KPI[1].toFixed(2));
                $("#velPro").text(json.Graph.KPI[2] + ' Km/h');
                $("#calif").text(json.Graph.KPI[3]);
                $("#hcarb").text(json.Graph.KPI[4] + ' KgCo2');
                $("#ccomb").text(json.Graph.KPI[5] + ' Gal');
                $("#arbol").text(json.Graph.KPI[6]);
            }else{
                $("#countVeh").text('--');
                $("#reccKms").text('--');
                $("#velPro").text('--');
                $("#calif").text('--');
                $("#hcarb").text('--');
                $("#ccomb").text('--');
                $("#arbol").text('--');
            }
            if(json.Graph.Graph1){
                generateGraphActFlota(json.Graph);
                generateGraphActFlota2(json.Graph);
                generateRadial(json.Graph.Prod);
                generateBarChart(json.Graph.Habitos);
                top5Format(json.Graph.Top5);
            }
        }
        a.order(orderConfig).draw();
    })   
    // If I do something to make the script error-out, it never goes inside of here. It always goes inside of the ".on('xhr.dt'...)". I want to handle success and error separately.
    .on('error.dt', function ( e, settings, techNote, message ) {
        loadingAlert.close();
        console.log('Uh-oh, that did not work...'); // Just as an example of what I'd want to be able to do
    });

    a.on('column-visibility.dt', function(e, settings, columnIdx, state) {
        var col = a.column(columnIdx);
        var header = $(col.header());

        var columnGroupsToProcess = [
            [0, [1, 2, 3]],
            [16, [17, 18, 20, 21]]
        ];
    
        if (state) {
            header.removeClass('not-export-col');
        } else {
            header.addClass('not-export-col');
        }
    
        columnGroupsToProcess.forEach(function(group) {
            var triggerIdx = group[0];
            var columns = group[1];
    
            if (columnIdx === triggerIdx) {
                columns.forEach(function(colIdx) {
                    $(a.column(colIdx).header()).toggleClass('not-export-col', !state);
                });
            }
        });
        
    });

    $(window).on('resize', function() {
        var visibleCols = $("#datatable-buttons").DataTable().columns(':visible').count();
        $('.group-header td').attr('colspan', visibleCols + 1);
    });

    $("#datatable-buttons").on('column-visibility.dt', function(e, settings, column, state) {
        var visibleCols = $("#datatable-buttons").DataTable().columns(':visible').count();
        $('.group-header td').attr('colspan', visibleCols + 1);
    });

    $('#datatable-buttons tbody').on('click', 'tr.group-header', function() {
        var current_row = $(this);
        var next_row = current_row.next();
        while (next_row.length && !next_row.hasClass('group-header')) {
          next_row.toggle();
          next_row = next_row.next();
        }
        current_row.find('.toggle-icon').toggleClass('hide');
      });
    a.order(orderConfig).draw();
    a.buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"), $(".dataTables_length select").addClass("form-select form-select-sm"), $("#selection-datatable").DataTable({
        select: {
            style: "multi"
        },
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        }
    }), $("#key-datatable").DataTable({
        keys: !0,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        }
    }), a.buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"), $(".dataTables_length select").addClass("form-select form-select-sm"), $("#alternative-page-datatable").DataTable({
        pagingType: "full_numbers",
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded"), $(".dataTables_length select").addClass("form-select form-select-sm")
        }
    }), $("#scroll-vertical-datatable").DataTable({
        scrollY: "350px",
        scrollCollapse: !0,
        paging: !1,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
        }
    }), $("#complex-header-datatable").DataTable({
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded"), $(".dataTables_length select").addClass("form-select form-select-sm")
        },
        columnDefs: [{
            visible: !1,
            targets: -1
        }]
    }), $("#state-saving-datatable").DataTable({
        stateSave: !0,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded"), $(".dataTables_length select").addClass("form-select form-select-sm")
        }
    })
}