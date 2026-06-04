<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\LinkPage;

use App\Models\LinkPage;
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
        /** @var LinkPage|null $page */
        $page = $this->route('linkPage') ?? $this->user()?->linkPage;
        $ignoreId = $page?->id;

        return [
            'title'           => ['sometimes', 'nullable', 'string', 'max:120'],
            'slug'            => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-z0-9](?:[a-z0-9\-_.]*[a-z0-9])?$/',
                Rule::unique(LinkPage::class, 'slug')->ignore($ignoreId),
            ],
            'bio'             => ['sometimes', 'nullable', 'string', 'max:500'],
            'theme_key'       => ['sometimes', 'nullable', 'string', 'max:32'],
            'theme_overrides' => ['sometimes', 'nullable', 'array'],
            'is_published'    => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'  => 'La URL solo admite minúsculas, números, guiones, puntos y guiones bajos.',
            'slug.unique' => 'Esa URL ya está en uso. Prueba con otra.',
        ];
    }
}
