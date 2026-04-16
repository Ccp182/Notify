

async function initMap() {
  //@ts-ignore
  const { Map } = await google.maps.importLibrary("maps");

  map = new Map(document.getElementById("map"), {
    center: { lat: -2.1528947389308892, lng: -79.89367752863713 },
    zoom: 8,
  });
}

initMap()

/*$(document).ready(function() {
  $("#my-btn").on('click', function() {
    console.log('boton presionado')
  });
});*/


/*$(document).ready(function() {
  var mainContent = document.querySelector('.main-content');
  lastMarginLeft=0;
  function actualizarPosicionElemento() {
    // Obtiene la posición actual de .main-content
    var marginLeft = parseInt(window.getComputedStyle(mainContent).getPropertyValue('margin-left'));
  
    // Actualiza la posición del elemento con respecto a .main-content
    var elemento = document.querySelector('.table-float');
    elemento.style.left = (marginLeft) + 'px';
  }
  
  // Función de intervalo para verificar periódicamente el valor del margen izquierdo
  setInterval(function() {
    // Obtiene la posición actual de .main-content
    var marginLeft = parseInt(window.getComputedStyle(mainContent).getPropertyValue('margin-left'));
  
    // Si el valor del margen izquierdo ha cambiado, actualiza la posición del elemento
    if (marginLeft !== lastMarginLeft) {
      actualizarPosicionElemento();
      lastMarginLeft = marginLeft;
    }
  }, 100); // Verifica cada 100 ms
  
  // Llama a la función por primera vez para inicializar la posición
  actualizarPosicionElemento();


});*/

// Función para actualizar la posición del elemento

