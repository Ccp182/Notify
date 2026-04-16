$('div.toolbar .dropdown-menu input[type="checkbox"]').on('change', function() {
    // Obtener el id del checkbox seleccionado sin la palabra "check"
    var checkboxId = $(this).attr('id').replace('check', '');
    
    // Obtener el elemento con la clase .Dir
    var dirElement = $('.Dir');
    
    // Verificar si el checkbox está marcado
    if ($(this).is(':checked')) {
        // Si está marcado, mostrar el elemento .Dir
        dirElement.css('display', 'block');
    } else {
        // Si no está marcado, ocultar el elemento .Dir
        dirElement.css('display', 'none');
    }
});

$(document).ready(function(){
    

});

