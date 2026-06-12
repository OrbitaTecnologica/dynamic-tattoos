<?php

declare(strict_types=1);

return [
    // Dirección que recibe los mensajes del formulario de contacto público.
    // Por defecto cae al remitente del sistema (MAIL_FROM_ADDRESS).
    'to' => env('CONTACT_TO_ADDRESS', env('MAIL_FROM_ADDRESS', 'info@dynamic-tattoos.com')),

    // Mailer (config/mail.php) que envía estos mensajes. Por defecto el mailer
    // dedicado 'contact', con su propio SMTP (CONTACT_MAIL_*).
    'mailer' => env('CONTACT_MAILER', 'contact'),

    // Remitente de los emails de contacto. Debe coincidir con la cuenta SMTP
    // autenticada (CONTACT_MAIL_USERNAME) o el servidor rechazará el envío.
    // El Reply-To se fija al email del visitante, no aquí.
    'from' => [
        'address' => env('CONTACT_MAIL_FROM_ADDRESS', env('CONTACT_MAIL_USERNAME', env('MAIL_FROM_ADDRESS', 'info@dynamic-tattoos.com'))),
        'name' => env('CONTACT_MAIL_FROM_NAME', 'Dynamic Tattoos'),
    ],
];
