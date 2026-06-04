<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\LinkPage;

use App\Services\LinkCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLinkRequest extends FormRequest
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
            'type'        => ['sometimes', 'string', Rule::in(LinkCatalog::types())],
            'value'       => ['sometimes', 'required', 'string', 'max:255'],
            'label'       => ['sometimes', 'nullable', 'string', 'max:80'],
            'custom_icon' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
