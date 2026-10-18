<?php

/*
|--------------------------------------------------------------------------
| Pantalla "Sin acceso" (resources/views/acceso/sin-permiso.blade.php)
|--------------------------------------------------------------------------
| La muestra el middleware AccesoNotify cuando el SSO responde
| SUBUSUARIO_NO_SOPORTADO: Notify es solo para el usuario principal
| (2026-10-17). Misma pantalla que GF y HMSeshat.
|
| contacto_pais: copia de HMSeshat config/seshat.php (contacto_pais). Si cambia
| un telefono o un horario, se cambia en los dos. Un pais sin entrada no
| muestra la tarjeta de contacto.
|
| La razon social del pie NO va aqui: sale de reportes_pdf.razon_social (si existe), la
| misma que firma los PDF.
*/

return [

    'contacto_pais' => [
        'EC' => [
            'telefono' => ['texto' => '(04) 600 4640', 'href' => 'tel:+59346004640'],
            'horario'  => 'Lunes a viernes, 8:30 a 17:00',
            'soporte'  => ['texto' => 'soportetecnico@carsegsa.com', 'href' => 'mailto:soportetecnico@carsegsa.com'],
            'correo'   => 'sacnacional@carsegsa.com',
        ],
        'PE' => [
            'telefono' => ['texto' => '(01) 618 5930', 'href' => 'tel:+5116185930'],
            'horario'  => 'Lunes a viernes, 8:30 a 18:30; sábados, 9:00 a 12:00',
            'soporte'  => ['texto' => '(01) 618 5939 · 24/7', 'href' => 'tel:+5116185939'],
            'correo'   => 'atencionalcliente@hunter.com.pe',
        ],
        'CO' => [
            'telefono' => ['texto' => '(601) 355 7656', 'href' => 'tel:+576013557656'],
            'horario'  => 'Lunes a viernes, 8:30 a 17:30; sábados, 8:30 a 12:00',
            'soporte'  => ['texto' => 'postventa@huntertec.com.co', 'href' => 'mailto:postventa@huntertec.com.co'],
            'correo'   => '',
        ],
        'CL' => [
            'telefono' => ['texto' => '600 3600 148', 'href' => 'tel:+566003600148'],
            'horario'  => 'Lunes a viernes, 9:30 a 19:30; sábados, 10:00 a 13:00',
            'soporte'  => ['texto' => '600 3600 138 · 24/7', 'href' => 'tel:+566003600138'],
            'correo'   => 'atencionalcliente@huntersat.cl',
        ],
    ],

];
