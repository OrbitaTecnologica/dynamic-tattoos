<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\ValidCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNotNull('email_verified_at'),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:100'],
            // Rol con el que se registra. El rol `artist` no se asigna aquí: pasa
            // por el flujo separado de TatuadorSolicitud + aprobación admin.
            'role' => ['nullable', 'string', 'in:user,ambassador'],
            'referral_code' => ['nullable', 'string', 'max:32'],
            // Slug del plan elegido en el landing (opcional). El gratis se asigna
            // en el registro; el de pago se confirma vía checkout/webhook.
            'plan' => ['nullable', 'string', 'max:100'],
            // `present` (no `nullable`): el campo debe venir siempre y ValidCaptcha lo
            // verifica. Así un bot que llame al API sin resolver el captcha es rechazado.
            // En dev (sin secret) ValidCaptcha no bloquea, así que no rompe local.
            'captcha_token' => ['present', new ValidCaptcha()],
        ];
    }
}
