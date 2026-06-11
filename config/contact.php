<?php

declare(strict_types=1);

return [
    // Dirección que recibe los mensajes del formulario de contacto público.
    // Por defecto cae al remitente del sistema (MAIL_FROM_ADDRESS).
    'to' => env('CONTACT_TO_ADDRESS', env('MAIL_FROM_ADDRESS', 'info@dynamic-tattoos.com')),
];
