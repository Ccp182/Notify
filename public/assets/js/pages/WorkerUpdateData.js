self.onmessage = function(event) {
    var formData = event.data.formData;
    var post = event.data.post;
    var csrfToken = event.data.csrfToken;

    // Realiza la solicitud AJAX incluyendo el token CSRF
    var xhr = new XMLHttpRequest();
    xhr.open('POST', post);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken); // Incluye el token CSRF en el encabezado
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText); // Parsea el texto JSON en un objeto JavaScript
            self.postMessage(response);
        } else {
            console.error('Error en la petición Ajax:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Error en la petición Ajax.');
    };
    xhr.send(formData);
};