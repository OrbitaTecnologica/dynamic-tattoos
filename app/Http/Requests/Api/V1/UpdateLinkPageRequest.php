<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLinkPageRequest extends FormRequest
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
        $pageId = $this->user()?->linkPage?->id;

        return [
            'slug' => [
                'sometimes', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/',
                Rule::unique('link_pages', 'slug')->ignore($pageId),
            ],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'theme_key' => ['sometimes', 'string', 'max:64'],
            'theme_overrides' => ['sometimes', 'nullable', 'array'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
