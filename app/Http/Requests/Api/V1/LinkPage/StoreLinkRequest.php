<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\LinkPage;

use App\Services\LinkCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreLinkRequest extends FormRequest
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
            'type'        => ['required', 'string', Rule::in(LinkCatalog::types())],
            'value'       => ['required', 'string', 'max:255'],
            'label'       => ['nullable', 'string', 'max:80'],
            'custom_icon' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
