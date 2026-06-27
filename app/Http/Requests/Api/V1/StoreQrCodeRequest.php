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
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._~\-\/]+$/', 'unique:qr_codes,slug'],
            'name' => ['nullable', 'string', 'max:150'],
            'url' => ['nullable', 'string', 'url', 'max:2048'],
            'color' => ['nullable', 'string', 'max:9'],
            'dots_type' => ['nullable', Rule::in(['dots', 'rounded', 'extra-rounded', 'square', 'classy', 'classy-rounded'])],
            'corners_square_type' => ['nullable', Rule::in(['dot', 'extra-rounded', 'square'])],
            'corners_dot_type' => ['nullable', Rule::in(['dot', 'square'])],
        ];
    }
}
