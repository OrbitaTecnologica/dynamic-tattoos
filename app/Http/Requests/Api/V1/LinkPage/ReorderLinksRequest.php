<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\LinkPage;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderLinksRequest extends FormRequest
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
            'ids'   => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'min:1'],
        ];
    }
}
