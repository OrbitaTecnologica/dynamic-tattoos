<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

final class ValidCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = (string) config('services.turnstile.secret');

        // Dev/test sin secret configurado: no bloquea.
        if ($secret === '') {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('La verificación anti-bot es obligatoria.');

            return;
        }

        $response = Http::asForm()->post((string) config('services.turnstile.verify_url'), [
            'secret' => $secret,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if ($response->json('success') !== true) {
            $fail('La verificación anti-bot ha fallado. Inténtalo de nuevo.');
        }
    }
}
