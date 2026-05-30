<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Services\LinkCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreLinkPageLinkRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(LinkCatalog::types())],
            'label' => ['nullable', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'custom_icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
