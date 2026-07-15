<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreQrCodeRequest extends FormRequest
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
            // El identificador (slug) lo genera el servidor de forma automática
            // (6 caracteres). No se acepta desde el cliente para evitar choques
            // de nombres entre usuarios. Ver QrCode::generateUniqueCode().
            'name' => ['nullable', 'string', 'max:150'],
            'url' => ['nullable', 'string', 'url', 'max:2048'],
            'color' => ['nullable', 'string', 'max:9'],
            'dots_type' => ['nullable', Rule::in(['dots', 'rounded', 'extra-rounded', 'square', 'classy', 'classy-rounded'])],
            'corners_square_type' => ['nullable', Rule::in(['dot', 'extra-rounded', 'square'])],
            'corners_dot_type' => ['nullable', Rule::in(['dot', 'square'])],
        ];
    }
}
