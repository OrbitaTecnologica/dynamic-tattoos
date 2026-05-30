<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePreferencesRequest extends FormRequest
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
            'language' => ['sometimes', Rule::in(['es', 'en', 'pt', 'fr', 'de'])],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'currency' => ['sometimes', Rule::in(['EUR', 'USD', 'GBP', 'MXN'])],
        ];
    }
}
