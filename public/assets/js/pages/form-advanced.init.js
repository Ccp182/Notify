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
  $("#colorButtonNoti").spectrum({
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

});


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

  $('#buttonTextNoti').on('input', function() {
      $('.button_noti .btn').text($(this).val() || 'Ver Más.');
  });


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
        $('.buttonNoti').removeClass("d-none");
    } else if ($(this).attr("href") === "#FullImageCardNoti") {
        $('.button_noti').addClass("d-none");
        $('#activateBtnNoti').prop("checked", false);
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
        $('.buttonNoti, .buttonNoti').addClass('d-none');
        $(".android_template_design").removeClass("topbanner");
    }else{
        $(".android_template_design").removeClass("topbanner");
        $('.buttonNoti').removeClass("d-none");
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
  



  $("#activateBtnNoti").on("change", function () {
    if ($(this).is(":checked")) {
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", false);
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", true);
        $('.button_noti').removeClass("d-none");
    } else {
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
        $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
        $('.button_noti').addClass("d-none");

    }
  });



  $(".tipoNotiClass .nav-link").on("click", function () {
    let tabId = $(this).attr("href"); // Obtiene el ID del tab (Ej: #SimpleNoti o #MultiNoti)
    console.log("Se hizo clic en: " + tabId);
    if (tabId === "#SimpleNoti") {
      $('.modelDesignNoti, .buttonNoti').addClass('d-none');
      $('#customFileNoti').prop("required", false);
      $('#activateBtnNoti').prop("checked", false);
      $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
      $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
      
      
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
      $('.modelDesignNoti, .buttonNoti').addClass('d-none');
      $('#customFileNoti').prop("required", false);
      $('#activateBtnNoti').prop("checked", false);
      $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("disabled", true);
      $("#buttonTextNoti, #urlNoti, #colorButtonNoti, #colorButtonTextNoti").prop("required", false);
      
      
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
           
        };
        reader.readAsDataURL(input.files[0]); // Leer la imagen como DataURL
    } else {
        $(".android_noti .img_noti, .ios_noti .img_noti").addClass("d-none");
        $(".android_noti .img_noti img, .ios_noti .img_noti img").attr("src", "{{ URL::asset('assets/images/Image-not-found.png') }}");
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
              url: postDispositivosSendNew,
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
        ProcessProgramation();
    } else {
      console.log("No se envía el formulario porque no está en la pestaña de 'Notificación'.");
    }

});






});
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

function ProcessProgramation(){
  $('.pager .next').click();
  $('.pager .previous_custom').removeClass("disabled");
  
  $('#programation_h').val($('#program').val());
  $('.programacion_text').text('Se enviará '+$('#program option:selected').text());
  $('.next_custom').addClass('d-none');
  $('#btnPublic').removeClass('d-none');
}
  
