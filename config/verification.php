<?php

declare(strict_types=1);

return [
    // Verificación de email por código OTP.
    'code_length' => 6,
    'ttl_minutes' => 15,
    'max_attempts' => 5,
    'resend_cooldown_seconds' => 60,
];
