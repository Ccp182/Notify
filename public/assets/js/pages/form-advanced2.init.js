const $container = $('.spacebtn');
const $btnAdd    = $('#btnAddNotiSection');

const program = $('#program');
const onceBox  = $('#schedule-once');
const $date     = $('#schedule_date');
const $time     = $('#schedule_time');

const dailyBox   = $('#schedule-daily');
const dailyTime  = $('#schedule_daily_time');
const dailyStart = $('#schedule_daily_start');
const dailyEnd   = $('#schedule_daily_end');

const customBox = $('#schedule-custom');
const customType = $('#custom_type');

const nEvery = $('#custom_every_n');
const nTime  = $('#custom_time_n');
const nStart = $('#custom_start_n');

const wTime  = $('#custom_week_time');
const wStart = $('#custom_week_start');
const wEnd   = $('#custom_week_end');

const mDay   = $('#custom_month_day');
const mTime  = $('#custom_month_time');
const mStart = $('#custom_month_start');


$(document).ready(function() {

  $('input#titleNoti').maxlength({
    threshold: 20,
    warningClass: "badge bg-info",
    limitReachedClass: "badge bg-warning"
  });
  $('textarea#subTitleNoti').maxlength({
    threshold: 20,
    warningClass: "badge bg-info",
    limitReachedClass: "badge bg-warning"
  }); 
  $('input#buttonTextNoti').maxlength({
    threshold: 20,
    warningClass: "badge bg-info",
    limitReachedClass: "badge bg-warning"
  });


  $("#colorTitleNoti").spectrum({
    color: "rgba(0, 0, 0, 1)", 
    showInput: true, 
    allowEmpty: false,
    showAlpha: false,
    preferredFormat: "rgba",
    move: function (color) {
        $(".modal_design .title_noti").css("color", color.toHexString());
    },
  });
  $("#colorSubTitleNoti").spectrum({
    color: "rgba(0, 0, 0, 1)", 
    showInput: true, 
    allowEmpty: false,
    showAlpha: false,
    preferredFormat: "rgba",
    move: function (color) {
        $(".modal_design .subTitle_noti").css("color", color.toHexString());
    },
  });
  $("#colorModalNoti").spectrum({
      color: "rgba(255, 255, 255, 1)", 
      showInput: true, 
      allowEmpty: false,
      showAlpha: false,
      preferredFormat: "rgba",
      move: function (color) {
          $(".modal_design").css("background-color", color.toHexString());
      },
  });
    /*$("#colorButtonNoti").spectrum({
        color: "#eeeeee", 
        showInput: true, 
        allowEmpty: false,
        showAlpha: false,
        preferredFormat: "rgba",
        move: function (color) {
            $(".button_noti .btn").css("background-color", color.toHexString());
        },
    });
    $("#colorButtonTextNoti").spectrum({
        color: "rgba(255, 255, 255, 1)", 
        showInput: true, 
        allowEmpty: false,
        showAlpha: false,
        preferredFormat: "rgba",
        move: function (color) {
            $(".button_noti .btn").css("color", color.toHexString());
        },

    });*/
    

    onProgramChange();
    program.on('change', onProgramChange);


    $('#filterDispositivo').on('submit',function(e){
        e.preventDefault();
        activeFilter=true;
        template=false;
        setDatatableDispositivosAlt();
    });

    $('#filterTemplateDispositivo').on('submit', function(e) {
    e.preventDefault();
    const archivo = $('#archivo')[0]?.files?.[0];
    if (!archivo) {
        Swal.fire({
            icon: 'warning',
            title: 'Archivo requerido',
            text: 'Por favor selecciona un archivo CSV.',
        });
        return;
    }
    const formData = new FormData();
    formData.append('archivo', archivo);
    $.ajax({
        url: postDispoTemplateSendNew,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Procesando archivo...',
                text: 'Por favor espera mientras se carga el archivo.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close(); // Cierra el loader
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el archivo',
                    text: response.message || 'Ocurrió un error al procesar el archivo.',
                });
                return;
            }
            $('#inputChasisList').val(response.chasis);
            $('#inputMotorList').val(response.motor);
            $('#inputNumList').val('');
            template=true;
            activeFilter=true;
            setDatatableDispositivosAlt();
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error en la carga',
                text: 'No se pudo cargar el archivo. Inténtalo nuevamente.',
            });
        }
    });
    });

    $('#filterTemplateNumDispositivo').on('submit', function(e) {
    e.preventDefault();
    const archivo = $('#archivoNum')[0]?.files?.[0];
    if (!archivo) {
        Swal.fire({
            icon: 'warning',
            title: 'Archivo requerido',
            text: 'Por favor selecciona un archivo CSV.',
        });
        return;
    }
    const formData = new FormData();
    formData.append('archivo', archivo);
    $.ajax({
        url: postDispoTemplateNumSendNew,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Procesando archivo...',
                text: 'Por favor espera mientras se carga el archivo.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close(); // Cierra el loader
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el archivo',
                    text: response.message || 'Ocurrió un error al procesar el archivo.',
                });
                return;
            }
            $('#inputNumList').val(response.celulares);
            $('#inputChasisList, #inputMotorList').val('');
            template=true;
            activeFilter=true;
            setDatatableDispositivosAlt();
        },
        error: function() {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error en la carga',
                text: 'No se pudo cargar el archivo. Inténtalo nuevamente.',
            });
        }
    });
    });

    $('#colorButtonNoti').prop("disabled", true);
    $('#colorButtonTextNoti').prop("disabled", true);

    $('#titleNoti').on('input', function() {
        $('.title_noti').text($(this).val() || 'Título de la notificación');
    });

    // Actualizar subtítulo en tiempo real
    $('#subTitleNoti').on('input', function() {
        $('.subTitle_noti').text($(this).val() || 'Descripción de la notificación');
    });

    /*$('#buttonTextNoti').on('input', function() {
        $('.button_noti .btn').text($(this).val() || 'Ver Más.');
    });*/


    $(".tipoDesignNotiClass .nav-link").on("click", function () {
        // Obtener el índice del tab clickeado (1 a 5)
        let index = $(this).parent().index() + 1;
        $('#tipoMode').val(index);
        // Verificar si el tab ya está activo
        if ($(this).hasClass("active")) {
            // Ocultar todos los modales
            $(".modal_design > div").addClass("d-none");
            
            // Mostrar el modal correspondiente
            $(".modal" + index).removeClass("d-none");
        }
        if ($(this).attr("href") === "#TopSideImageCardNoti") {
            $(".android_template_design").addClass("topbanner");
            
        } else if ($(this).attr("href") === "#FullImageCardNoti") {
            $(".android_template_design").removeClass("topbanner");
            $('.button_noti_pri, .button_noti_sec').addClass("d-none");
            $('.buttonNoti').addClass('d-none');
            setButtonConfigEnabled(false);
        }else{
            $(".android_template_design").removeClass("topbanner");
            if ($('#tipoNoti').val() === '1') {
                const hasPri = $('.buttonNoti .noti-btn-section[data-key="principal"]').length > 0;
                const hasSec = $('.buttonNoti .noti-btn-section[data-key="secundario"]').length > 0;
                $('.buttonNoti').removeClass('d-none');
                if (hasPri) $('.button_noti_pri').removeClass("d-none");
                if (hasSec) $('.button_noti_sec').removeClass("d-none");
                setButtonConfigEnabled(true);
            } else {
                setButtonConfigEnabled(false);
                $('.buttonNoti').addClass('d-none');
                $('.button_noti_pri, .button_noti_sec').addClass("d-none");
            }
        }
    });


    $("#colorTitleNoti").on("input", function () {
        let color = $(this).val();
        $(".modal_design .title_noti").css("color", color);
    });
    $("#colorSubTitleNoti").on("input", function () {
        let color = $(this).val();
        $(".modal_design .subTitle_noti").css("color", color);
    });
    $("#colorModalNoti").on("input", function () {
        let color = $(this).val();
        $(".modal_design").css("background-color", color);
    });
  

    $('#schedule_date, #schedule_time').on('change input', function () {
    const timeEl = document.getElementById('schedule_time');
    if (timeEl) timeEl.setCustomValidity('');
    });

    $('#schedule_daily_start, #schedule_daily_end').on('input change', function () {
        const el = document.getElementById('schedule_daily_end');
        if (el) el.setCustomValidity('');
    });

    /*$("#activateBtnNoti").on("change", function () {
        if ($(this).is(":checked")) {
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", false);
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", true);
            $('.button_noti').removeClass("d-none");
        } else {
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
            $('.button_noti').addClass("d-none");

        }
    });*/



    $(".tipoNotiClass .nav-link").on("click", function () {
        let tabId = $(this).attr("href"); // Obtiene el ID del tab (Ej: #SimpleNoti o #MultiNoti)
        console.log("Se hizo clic en: " + tabId);
        if (tabId === "#SimpleNoti") {
            setButtonConfigEnabled(false);
            $('.modelDesignNoti, .buttonNoti').addClass('d-none');
            $('#customFileNoti').prop("required", false);
            //$('#activateBtnNoti').prop("checked", false);
            //$("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
            //$("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
            
            
            $('.TwoNoti').addClass("d-none");
            $('#collapseTwoNoti').removeClass("show");
            $('a[href="#collapseTwoNoti"]').addClass("collapsed").attr("aria-expanded", "false");
            $('#collapseOneNoti').addClass("show");
            $('a[href="#collapseOneNoti"]').removeClass("collapsed").attr("aria-expanded", "true");

            $('#opImgNoti').text('(opcional)');
            //$('#revision .rev_content_visual').html($('#collapseTwoNoti').html());
            $('#tipoNoti').val('0');
            $('#htmlNoti').prop("required", false);
            $('.hmtlSetNoti').addClass("d-none");
        } else if (tabId === "#MultiNoti") {
            setButtonConfigEnabled(true);
            $('.modelDesignNoti, .buttonNoti').removeClass('d-none');
            $('#customFileNoti').prop("required", true);

            $('.TwoNoti').removeClass("d-none");
            $('#collapseOneNoti').removeClass("show");
            $('a[href="#collapseOneNoti"]').addClass("collapsed").attr("aria-expanded", "false");
            $('#collapseTwoNoti').addClass("show");
            $('a[href="#collapseTwoNoti"]').removeClass("collapsed").attr("aria-expanded", "true");

            $('#opImgNoti').text('');
            //$('#revision .rev_content_visual').html($('#collapseOneNoti').html());
            $('#tipoNoti').val('1');
            $('#htmlNoti').prop("required", false);
            $('.hmtlSetNoti').addClass("d-none");
        } else if (tabId === "#HTMLNoti") {
            setButtonConfigEnabled(false);
            $('.modelDesignNoti, .buttonNoti').addClass('d-none');
            $('#customFileNoti').prop("required", false);
            /*$('#activateBtnNoti').prop("checked", false);
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
            $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);*/
            
            
            $('.TwoNoti').addClass("d-none");
            $('#collapseTwoNoti').removeClass("show");
            $('a[href="#collapseTwoNoti"]').addClass("collapsed").attr("aria-expanded", "false");
            $('#collapseOneNoti').addClass("show");
            $('a[href="#collapseOneNoti"]').removeClass("collapsed").attr("aria-expanded", "true");

            $('#opImgNoti').text('(opcional)');
            //$('#revision .rev_content_visual').html($('#collapseTwoNoti').html());
            $('#tipoNoti').val('2');
            $('#htmlNoti').prop("required", true);
            $('.hmtlSetNoti').removeClass("d-none");
        
        }

    });


    $("#customFileNoti").on("change", function (event) {
        let input = event.target;
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $(".android_noti .img_noti, .ios_noti .img_noti").removeClass("d-none");

                $(".android_noti .img_noti img, .ios_noti .img_noti img").attr("src", e.target.result);
                $(".modal1 img.img_noti, .modal2 img.img_noti, .modal3 img.img_noti, .modal4 img.img_noti, .modal5 img.img_noti").attr("src", e.target.result);
                
            };
            reader.readAsDataURL(input.files[0]); // Leer la imagen como DataURL
        } else {
            $(".android_noti .img_noti, .ios_noti .img_noti").addClass("d-none");
            $(".android_noti .img_noti img, .ios_noti .img_noti img").attr("src", "{{ URL::asset('assets/images/Image-not-found.png') }}");
            $(".modal1 img.img_noti, .modal2 img.img_noti, .modal3 img.img_noti, .modal4 img.img_noti, .modal5 img.img_noti").attr("src", "{{ URL::asset('assets/images/Image-not-found.png') }}");
        }
    });


    $('#designfrm').on('submit',function(e){
        e.preventDefault();
        $('.pager .next').click();
        $('.pager .previous_custom').removeClass("disabled");
        $('#accordion-visualization2').html($('#accordion-visualization').html());
        $('.subtitle_input_text').text($('#subTitleNoti').val());
        if ($('.nav-item a[href="#SimpleNoti"]').hasClass("active")) {
        /*$('.TwoNoti').addClass("d-none");
        $('#collapseTwoNoti').removeClass("show");
        $('a[href="#collapseTwoNoti"]').addClass("collapsed").attr("aria-expanded", "false");
        $('#collapseOneNoti').addClass("show");
        $('a[href="#collapseOneNoti"]').removeClass("collapsed").attr("aria-expanded", "true");*/
        }else{
        /*$('.TwoNoti').removeClass("d-none");
        $('#collapseOneNoti').removeClass("show");
        $('a[href="#collapseOneNoti"]').addClass("collapsed").attr("aria-expanded", "false");
        $('#collapseTwoNoti').addClass("show");
        $('a[href="#collapseTwoNoti"]').removeClass("collapsed").attr("aria-expanded", "true");*/
        }
    });


    $('#programationfrm').on('submit',function(e){
        e.preventDefault();
        ProcessProgramation();
            
        
    });

    $('#NotificationSendfrm').on('submit', function (e) {
    e.preventDefault();
    




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
            var formData = new FormData(this);
            var designFormData = new FormData($('#designfrm')[0]);
            for (var pair of designFormData.entries()) {
                formData.append(pair[0], pair[1]); 
            }
            
            var selectedRowsData = tableData.rows({ selected: true }).data();
            var rowDataToSend = selectedRowsData.toArray().map(row => ({
                Name: row.Name,
                DId: row.DId,
                App: row.App,
                Min: row.Min
            }));
            console.log(JSON.stringify(rowDataToSend));
            formData.append('dispositivos-input', JSON.stringify(rowDataToSend));
            console.log([...formData]);
            $.ajax({
                type: "POST",
                url: postDispositivosSendNew2,
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
                                $("#designfrm").reset();
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

function resetScheduleFields() {
    // ONCE
    $date.prop('required', false).val('');
    $time.prop('required', false).val('');

    // DAILY
    dailyTime.prop('required', false).val('');
    dailyStart.prop('required', false).val('');
    dailyEnd.prop('required', false).val('');

    // Limpia mensajes HTML5 personalizados 
    if ($time[0]) $time[0].setCustomValidity('');
    if (dailyEnd[0]) dailyEnd[0].setCustomValidity('');
}

function onProgramChange() {
    const val = program.val();

    hideAllScheduleBoxes();
    resetAllRequired();
    resetScheduleFields();

    // AHORA
    if (val === '0') {
        return;
    }

    // PROGRAMADO (ONCE)
    if (val === '1') {
        onceBox.removeClass('d-none');
        $date.prop('required', true);
        $time.prop('required', true);
        return;
    }

    // DIARIO (FUTURO)
    if (val === '2') {
        dailyBox.removeClass('d-none');
        dailyTime.prop('required', true);
        dailyStart.prop('required', true);
        return;
    }

    // CUSTOM
    if (val === '3') {
        customBox.removeClass('d-none');
        customType.prop('required', true);
        $('#custom_type').trigger('change');

        return;
    }
}


    $('#btnPublic').on('click',function(e){
    $('#NotificationSendfrm').trigger('submit');
    });


$('.pager .previous_custom').on('click',function(e){
  $('.pager .previous').click();
  $('.next_custom').removeClass('d-none');
  $('#btnPublic').addClass('d-none');
});

$('#progrss-wizard .next_custom').on("click", function () {

    if ($('.nav-item a[href="#design"]').hasClass("active")) {
        let form = document.getElementById("designfrm");
        if (form.checkValidity()) {
            setDatatableDispositivosAlt(); // Inicializa DataTable
            $('#designfrm').trigger('submit');
        } else {
            form.reportValidity();
        }
    }else if ($('.nav-item a[href="#audience"]').hasClass("active")) {
        let selectedDevices = tableData.rows({ selected: true }).count(); 
        if (selectedDevices > 0) {
            ProcessDatatableDisp();
        } else {
            alert('Para poder enviar notificaciones se debe seleccionar al menos un dispositivo de la lista.');
        }
    }else if ($('.nav-item a[href="#programation"]').hasClass("active")) {
        let form = document.getElementById("programationfrm");
        if (form.checkValidity()) {
            $('#programationfrm').trigger('submit');
        } else {
            form.reportValidity();
        }
    } else {
      console.log("No se envía el formulario porque no está en la pestaña de 'Notificación'.");
    }

});


$('#custom_type').on('change', function () {
    const v = $(this).val();

    // ocultar sub-bloques
    $('#custom-every-n-days, #custom-weekly, #custom-monthly').addClass('d-none');

    // limpiar required de todos los campos custom
    nEvery.prop('required', false);
    nTime.prop('required', false);
    nStart.prop('required', false);

    wTime.prop('required', false);
    wStart.prop('required', false);
    // wEnd opcional

    mDay.prop('required', false);
    mTime.prop('required', false);
    mStart.prop('required', false);

    // mostrar + required por tipo
    if (v === 'every_n_days') {
        $('#custom-every-n-days').removeClass('d-none');
        nEvery.prop('required', true);
        nTime.prop('required', true);
        nStart.prop('required', true);
    }

    if (v === 'weekly') {
        $('#custom-weekly').removeClass('d-none');
        wTime.prop('required', true);
        wStart.prop('required', true);
    }

    if (v === 'monthly') {
        $('#custom-monthly').removeClass('d-none');
        mDay.prop('required', true);
        mTime.prop('required', true);
        mStart.prop('required', true);
    }
});


    /**SECCIÓN BOTON NOTIFICACION */
    

    const blocks = [
        { key: 'principal',  title: 'Botón Principal' },
        { key: 'secundario', title: 'Botón Secundario' }
    ];

    $btnAdd.on('click', function () {
        const n = countBlocks();
        if (n >= 2) { updateAddState(); return; }

        const next = blocks[n];
        $container.append(buildBlock(next.key, next.title));

        const $newSection = $container.find(`.noti-btn-section[data-key="${next.key}"]`);

        // Estado inicial UI tipo (por defecto url)
        applyTipoUI($newSection);

        // ✅ Mostrar preview automático (PRI o SEC)
        showPreview(next.key);
        
        // ✅ Inicia spectrum SOLO para ese bloque (y pinta preview del key)
        initSpectrumForSection($newSection);

        // ✅ Inicializa texto/colores del preview con los valores actuales del bloque
        initPreviewFromSection($newSection);

        updateAddState();
    });

    $container.on('change', 'select[id^="selectTipoNoti_"]', function () {
        const $section = $(this).closest('.noti-btn-section');
        applyTipoUI($section);
    });

    $container.on('input', 'input[id^="buttonTextNoti_"]', function () {
        const $section = $(this).closest('.noti-btn-section');
        const key = $section.data('key');
        updatePreviewText(key, $(this).val());
    });

    // (Opcional) permitir quitar y reordenar para que siempre sea Principal primero y Secundario segundo
    $container.on('click', '.btnRemoveNotiSection', function () {
        const $section = $(this).closest('.noti-btn-section');
        const removedKey = $section.data('key'); // principal | secundario

        // destruir spectrum antes de remover
        destroySpectrumForSection($section);

        // ✅ Ocultar preview del bloque eliminado
        hidePreview(removedKey);

        $section.remove();

        // Si quieres mantener la regla "si queda solo uno, que sea principal":
        const $only = $container.find('.noti-btn-section');
        if ($only.length === 1 && $only.data('key') === 'secundario') {
            // Convertir secundario -> principal manteniendo valores
            const $sec = $only;

            const values = {
                text: $sec.find('#buttonTextNoti_secundario').val(),
                type: $sec.find('#selectTipoNoti_secundario').val(),
                url: $sec.find('#urlNoti_secundario').val(),
                phone: $sec.find('#phoneNoti_secundario').val(),
                bg: $sec.find('#colorButtonNoti_secundario').val(),
                text_color: $sec.find('#colorButtonTextNoti_secundario').val(),
            };

            destroySpectrumForSection($sec);

            $sec.replaceWith(buildBlock('principal', 'Botón Principal'));
            const $newP = $container.find('.noti-btn-section[data-key="principal"]');

            $newP.find('#buttonTextNoti_principal').val(values.text);
            $newP.find('#selectTipoNoti_principal').val(values.type);
            $newP.find('#urlNoti_principal').val(values.url);
            $newP.find('#phoneNoti_principal').val(values.phone);
            $newP.find('#colorButtonNoti_principal').val(values.bg);
            $newP.find('#colorButtonTextNoti_principal').val(values.text_color);

            applyTipoUI($newP);
            initSpectrumForSection($newP);

            // ✅ Ajustar previews por conversión:
            // el secundario ya no existe visualmente, ahora es principal
            hidePreview('secundario');
            showPreview('principal');
            initPreviewFromSection($newP);
        }

        updateAddState();
    });

    updateAddState();

});

function setButtonConfigEnabled(enabled) {
  const $scope = $('.buttonNoti');
  const $fields = $scope.find('input, select, textarea');

  if (!enabled) {
    $fields.each(function () {
      const $el = $(this);

      if ($el.prop('required')) $el.data('was-required', true);

      $el.prop('required', false);

      $el.prop('disabled', true);
    });
  } else {

    $fields.prop('disabled', false);
    refreshButtonConfigRequired();
  }
}

function refreshButtonConfigRequired() {
  $('.buttonNoti .noti-btn-section').each(function () {
    const $section = $(this);
    applyTipoUI($section);
    const key = $section.data('key'); // principal|secundario
    $section.find(`#buttonTextNoti_${key}`).prop('required', true);
    $section.find(`#selectTipoNoti_${key}`).prop('required', true);

    $section.find(`#colorButtonNoti_${key}`).prop('required', true);
    $section.find(`#colorButtonTextNoti_${key}`).prop('required', true);
  });
}

function countBlocks() {
    return $container.find('.noti-btn-section').length;
}

function updateAddState() {
    const isDisabled = countBlocks() >= 2;
    $btnAdd.prop('disabled', isDisabled);

    if (isDisabled) {
        $btnAdd.removeClass('btn-success').addClass('btn-light');
    } else {
        $btnAdd.removeClass('btn-light').addClass('btn-success');
    }
}

function initPreviewFromSection($section){
    const key = $section.data('key');
    showPreview(key);
    updatePreviewText(key, $section.find(`#buttonTextNoti_${key}`).val());
    updatePreviewBg(key, $section.find(`#colorButtonNoti_${key}`).val() || "#eeeeee");
    updatePreviewTextColor(key, $section.find(`#colorButtonTextNoti_${key}`).val() || "#000000");
}

function ProcessDatatableDisp(){

  $('.pager .next').click();
  $('.pager .previous_custom').removeClass("disabled");
  let tableN = $('#datatable-dispositivos-alt').DataTable(); // Obtiene la instancia de DataTable
  let totalDevices = tableN.rows().count(); // Total de filas en toda la tabla (sin importar la paginación)
  let selectedDevices = tableN.rows({ selected: true }).count(); // Total seleccionados en toda la tabla

  if (selectedDevices === totalDevices) {
      $('.count_datatable').text('Se ha seleccionado todo el segmento de usuarios');
  } else {
      $('.count_datatable').text('Se ha seleccionado ' + selectedDevices + ' del segmento de usuarios');
  }
}

function validateScheduleDateTime() {
    const program = $('#program').val(); // 0 NOW, 1 PROGRAMADO
    if (program !== '1') {
        return true;
    }

    const dateVal = $('#schedule_date').val();
    const timeVal = $('#schedule_time').val();

    if (!dateVal || !timeVal) {
        return true; // HTML5 required ya se encarga
    }
    const selectedDateTime = new Date(`${dateVal}T${timeVal}:00`);

    // Fecha/hora actual (con un pequeño margen de 1 minuto)
    const now = new Date();
    now.setSeconds(0, 0);

    if (selectedDateTime < now) {
        // Mensaje HTML5 nativo
        $('#schedule_time')[0].setCustomValidity(
            'La fecha y hora de envío no pueden ser menores a la fecha y hora actual.'
        );
        $('#schedule_time')[0].reportValidity();
        return false;
    } else {
        // Limpia el error si ya es válido
        $('#schedule_time')[0].setCustomValidity('');
        return true;
    }
}

function validateScheduleDailyRange() {
    const program = $('#program').val();
    if (program !== '2') return true;

     const start = $('#schedule_daily_start').val();
    const end = $('#schedule_daily_end').val();


    // Si no hay end, ok (si lo manejas opcional)
    if (!end) return true;

    if (start && end < start) {
        const endEl = document.getElementById('schedule_daily_end');
        endEl.setCustomValidity('La fecha fin no puede ser menor que la fecha inicio.');
        endEl.reportValidity();
        return false;
    }

    const endEl = document.getElementById('schedule_daily_end');
    endEl.setCustomValidity('');
    return true;
}

function validateCustomWeeklyDays() {
  if ($('#custom_type').val() !== 'weekly') return true;

  const anyChecked = $('.custom_week_day:checked').length > 0;

  // Usamos un input "ancla" para reportar el mensaje (custom_week_time, por ejemplo)
  const el = document.getElementById('custom_week_time');
  if (!el) return true;

  if (!anyChecked) {
    el.setCustomValidity('Selecciona al menos un día de la semana.');
    el.reportValidity();
    return false;
  }

  el.setCustomValidity('');
  return true;
}

function resetAllRequired() {
  // ONCE
  $date.prop('required', false);
  $time.prop('required', false);

  // DAILY
  dailyTime.prop('required', false);
  dailyStart.prop('required', false);
  dailyEnd.prop('required', false); // si lo manejas opcional, déjalo false

  // CUSTOM
  customType.prop('required', false);

  nEvery.prop('required', false);
  nTime.prop('required', false);
  nStart.prop('required', false);

  wTime.prop('required', false);
  wStart.prop('required', false);
  wEnd.prop('required', false); // opcional

  mDay.prop('required', false);
  mTime.prop('required', false);
  mStart.prop('required', false);
}

function hideAllScheduleBoxes() {
  onceBox.addClass('d-none');
  dailyBox.addClass('d-none');
  customBox.addClass('d-none');
}


$('#schedule_daily_start, #schedule_daily_end').on('change input', function () {
  const endEl = document.getElementById('schedule_daily_end');
  if (endEl) endEl.setCustomValidity('');
});


function ProcessProgramation(){
    const program = $('#program').val();
    // ✅ ONCE: no menor a ahora (solo si program = 1)
    if (!validateScheduleDateTime()) return;

    // ✅ DAILY: rango de fechas (solo si program = 2)
    if (!validateScheduleDailyRange()) return;
    // ✅ CUSTOM: (solo si program = 3)
    if (program === '3') {
        if (!validateCustomWeeklyDays()) return;
        buildCustomRuleJson();
    } else {
        $('#custom_rule_json_h').val(''); // si no es custom, vacía
    }
    
     // 2) Avanza wizard
    $('.pager .next').click();
    $('.pager .previous_custom').removeClass("disabled");

    // 3) Copiar a hiddens (los que ya tienes)
    $('#programation_h').val($('#program').val());
    $('#schedule_date_h').val($('#schedule_date').val());
    $('#schedule_time_h').val($('#schedule_time').val());

    // daily hidden (si ya los creaste)
    $('#schedule_daily_time_h').val($('#schedule_daily_time').val());
    $('#schedule_daily_start_h').val($('#schedule_daily_start').val());
    $('#schedule_daily_end_h').val($('#schedule_daily_end').val());

    // campaña hidden
    $('#titleCampaing_h').val($('#titleCampaing').val());
    $('#subTitleCampaing_h').val($('#subTitleCampaing').val());

    $('.programacion_text').text('Se enviará ' + $('#program option:selected').text());
    $('.next_custom').addClass('d-none');
    $('#btnPublic').removeClass('d-none');
}

function applyTipoUI($section) {
    const key  = $section.data('key');
    const tipo = $section.find(`#selectTipoNoti_${key}`).val(); // url | whatsapp | call

    const $wrapUrl   = $section.find(`.wrap-url[data-key="${key}"]`);
    const $wrapPhone = $section.find(`.wrap-phone[data-key="${key}"]`);

    const $url   = $section.find(`#urlNoti_${key}`);
    const $phone = $section.find(`#phoneNoti_${key}`);

    if (tipo === 'url') {
      $wrapUrl.removeClass('d-none');
      $wrapPhone.addClass('d-none');

      // required
      $url.prop('required', true);
      $phone.prop('required', false).val('');
    } else {
      $wrapUrl.addClass('d-none');
      $wrapPhone.removeClass('d-none');

      // required
      $url.prop('required', false).val('');
      $phone.prop('required', true);
    }
}

function buildCustomRuleJson() {
    const type = $('#custom_type').val();
    let rule = null;

    // Limpia por defecto
    $('#custom_rule_json_h').val('');

    if (!type) return;

    // =========================
    // Cada N días
    // =========================
    if (type === 'every_n_days') {
        const n     = Number($('#custom_every_n').val());
        const time  = $('#custom_time_n').val();      // "HH:MM"
        const start = $('#custom_start_n').val();     // "YYYY-MM-DD"

        rule = {
        freq: 'every_n_days',
        n: n,
        time: time,
        start: start
        };
    }

    // =========================
    // Semanal
    // =========================
    if (type === 'weekly') {
        const days = [];
        $('.custom_week_day:checked').each(function () {
        days.push(Number(this.value)); // 0..6 (Dom..Sab) según tu value
        });

        const time  = $('#custom_week_time').val();
        const start = $('#custom_week_start').val();
        const end   = $('#custom_week_end').val() || null;

        rule = {
        freq: 'weekly',
        days: days,
        time: time,
        start: start,
        end: end
        };
    }

    // =========================
    // Mensual
    // =========================
    if (type === 'monthly') {
        const day   = Number($('#custom_month_day').val()); // 1..31
        const time  = $('#custom_month_time').val();
        const start = $('#custom_month_start').val();

        rule = {
        freq: 'monthly',
        day: day,
        time: time,
        start: start
        };
    }

    // Guarda JSON (si existe)
    if (rule) {
        $('#custom_rule_json_h').val(JSON.stringify(rule));
    }
}



function initSpectrumForSection($section) {
    const key = $section.data('key');

    const $bg   = $section.find(`#colorButtonNoti_${key}`);
    const $text = $section.find(`#colorButtonTextNoti_${key}`);

    try { $bg.spectrum("destroy"); } catch(e) {}
    try { $text.spectrum("destroy"); } catch(e) {}

    $bg.spectrum({
        color: $bg.val() || "#eeeeee",
        showInput: true,
        allowEmpty: false,
        showAlpha: false,
        preferredFormat: "rgba",
        move: function (color) {
        updatePreviewBg(key, color.toHexString());
        },
        change: function (color) {
        updatePreviewBg(key, color.toHexString());
        }
    });

    $text.spectrum({
        color: $text.val() || "#000000",
        showInput: true,
        allowEmpty: false,
        showAlpha: false,
        preferredFormat: "rgba",
        move: function (color) {
        updatePreviewTextColor(key, color.toHexString());
        },
        change: function (color) {
        updatePreviewTextColor(key, color.toHexString());
        }
    });

    // aplica estado inicial a la vista previa
    updatePreviewBg(key, ($bg.val() || "#eeeeee"));
    updatePreviewTextColor(key, ($text.val() || "#000000"));
}

function destroySpectrumForSection($section) {
    const key = $section.data('key');
    try { $section.find(`#colorButtonNoti_${key}`).spectrum("destroy"); } catch(e) {}
    try { $section.find(`#colorButtonTextNoti_${key}`).spectrum("destroy"); } catch(e) {}
}

function getPreviewSelectorByKey(key){
    return (key === 'principal') ? '.button_noti_pri' : '.button_noti_sec';
}

function showPreview(key){
    $(getPreviewSelectorByKey(key)).removeClass('d-none');
}

function hidePreview(key){
    $(getPreviewSelectorByKey(key)).addClass('d-none');
}

function updatePreviewText(key, text){
    const t = (text && text.trim()) ? text : 'Ver Más.';
    $(getPreviewSelectorByKey(key)).find('.btn').text(t);
}

function updatePreviewBg(key, hex){
    $(getPreviewSelectorByKey(key)).find('.btn').css('background-color', hex);
}

function updatePreviewTextColor(key, hex){
    $(getPreviewSelectorByKey(key)).find('.btn').css('color', hex);
}

function buildBlock(key, title) {
    return `
      <div class="row noti-btn-section border rounded p-3 m-2 mb-3" data-key="${key}">
        <div class="col-12 d-flex justify-content-between align-items-center mb-2">
          <strong>${title}</strong>
          <button type="button" class="btn btn-outline-danger btn-sm btnRemoveNotiSection">
            Quitar
          </button>
        </div>

        <div class="col-lg-7">
          <div class="mb-2">
            <label class="form-label" for="buttonTextNoti_${key}">Texto del Botón</label>
            <input class="form-control"
                   type="text"
                   placeholder="Ver más"
                   id="buttonTextNoti_${key}"
                   name="buttons[${key}][text]"
                   maxlength="20"
                   required>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="mb-2">
            <label class="form-label" for="selectTipoNoti_${key}">Tipo Botón</label>
            <select id="selectTipoNoti_${key}"
                    name="buttons[${key}][type]"
                    class="form-select"
                    required>
              <option value="url" selected>Url</option>
              <option value="call">Call</option>
              <option value="whatsapp">WhatsApp</option>
            </select>
          </div>
        </div>

        <!-- URL -->
        <div class="col-lg-12 wrap-url" data-key="${key}">
          <div class="mb-2">
            <label class="form-label" for="urlNoti_${key}">Url Botón</label>
            <input class="form-control"
                   type="url"
                   placeholder="https://huntermonitoreo.com"
                   id="urlNoti_${key}"
                   name="buttons[${key}][url]"
                   required>
          </div>
        </div>

        <!-- PHONE (Call/WhatsApp) -->
        <div class="col-lg-12 wrap-phone d-none" data-key="${key}">
          <div class="mb-2">
            <label class="form-label" for="phoneNoti_${key}">Número celular</label>
            <input class="form-control"
                   type="tel"
                   placeholder="Ej: 0999999999 o +593999999999"
                   id="phoneNoti_${key}"
                   name="buttons[${key}][phone]"
                   inputmode="tel">
          </div>
        </div>

        <div class="col-lg-4">
          <div class="mb-2">
            <label class="form-label" for="colorButtonNoti_${key}">Fondo Botón</label>
            <input type="text"
                   class="form-control"
                   id="colorButtonNoti_${key}"
                   name="buttons[${key}][bg]"
                   value="#eeeeee"
                   required>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="mb-2">
            <label class="form-label" for="colorButtonTextNoti_${key}">Texto Botón</label>
            <input type="text"
                   class="form-control"
                   id="colorButtonTextNoti_${key}"
                   name="buttons[${key}][text_color]"
                   value="#000000"
                   required>
          </div>
        </div>

      </div>
    `;
}
  
