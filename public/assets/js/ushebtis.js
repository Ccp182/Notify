// Ushebtis.js

/**
 * Clase Ushebtis
 * Autor: Carlos Carpio
 * Descripción: Esta clase permite mostrar mensajes relacionados con el mantenimiento
 * e incidencias de la plataforma Hunter Monitoreo.
 */
class Ushebtis {
    constructor(codeApp) {
        this.CodeApp = codeApp; // Asigna el código de la aplicación a la propiedad CodeApp
        
    }

    

    
    /**
     * Muestra un mensaje de mantenimiento o incidencia relacionado con la plataforma.
     * @param {string} countryCode - El código de país para obtener el mensaje correspondiente.
     */
    showMessage(countryCode) {
        const url = `https://ushebtis.24hm.net/getInfoMessage/${countryCode.toLowerCase()}/${this.CodeApp}`;
        console.log(url);
    
        $.get(url, (data) => {
            if (data.Error === false && data.InfoMessage) {
                const now = new Date();
                const endTime = new Date(data.InfoMessage.end_time);
    
                if (now <= endTime) {
                    let message = data.InfoMessage.body || '';
                    let title = data.InfoMessage.title || '';
                    let image = data.InfoMessage.image || '';
                    let tipo = data.InfoMessage.type || '';
    
                    //image=null;
                    image='uploads/20250611_103840_20250611GEN.jpg';
                    //title='';
                    //message='';
                    //tipo="INCIDENCIA";
    
                    if (image) {

                        let options = {
                            html: `
                                <img src="https://ushebtis.24hm.net/${image}" style="max-width:100%; margin-bottom:5px;" />
                                <p style="text-align:left; font-size:14px;">${message}</p>
                            `,
                            width: 500,
                            padding: '2em',
                            confirmButtonText: 'No mostrar de nuevo',
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            title: title && title.trim() ? title.trim() : undefined,
                            text: message && message.trim() ? message.trim() : undefined,
                            preConfirm: () => {
                                localStorage.setItem('mensajeCerrado', 'true');
                            }
                        };
                        
                        Swal.fire(options);
                        
                    } else {
                        // Mensajes simples sin imagen, pero con ícono y mensaje centrado
                        let options = {
                            icon: tipo === 'INCIDENCIA' ? 'warning' : 'info',
                            confirmButtonText: 'No mostar de nuevo',
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            title: title && title.trim() ? title.trim() : undefined,
                            text: message && message.trim() ? message.trim() : undefined,
                            preConfirm: () => {
                                localStorage.setItem('mensajeCerrado', 'true');
                            }
                        };
                        
                        Swal.fire(options);
                        
                    }
    
                } else {
                    console.log("Ushebtis: No hay mensajes disponibles en este momento.");
                }
            } else {
                console.log("Ushebtis: No se encontraron datos válidos.");
            }
        });
    }
    

    
}

// Función para mostrar el mensaje al cargar la página
            
function mostrarMensajeAlCargar() {
    // Verifica si el mensaje ya se ha cerrado anteriormente
    if (!localStorage.getItem('mensajeCerrado')) {
        // Crea una instancia de Ushebtis
        const ushebtis = new Ushebtis(codeApp);// Crea una instancia con el código de aplicación 27(Gestor de Flotas) cambiar por el suyo
        
        // Llama al método showMessage con el código de país (puedes cambiarlo según tus necesidades)
        ushebtis.showMessage(countryApp); // Ejemplo con código de país 'ec' (Ecuador),'pe' (Perú),'co' (Colombia)
    }
}

