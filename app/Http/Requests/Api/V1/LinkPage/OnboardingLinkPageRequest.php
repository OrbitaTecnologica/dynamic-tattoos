<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\LinkPage;

use App\Models\LinkPage;
use App\Services\LinkCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OnboardingLinkPageRequest extends FormRequest
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
        $types = LinkCatalog::types();

        return [
            'title'     => ['required', 'string', 'max:120'],
            'slug'      => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-z0-9](?:[a-z0-9\-_.]*[a-z0-9])?$/',
                Rule::unique(LinkPage::class, 'slug'),
            ],
            'bio'       => ['nullable', 'string', 'max:500'],
            'theme_key' => ['nullable', 'string', 'max:32'],
            'links'                => ['nullable', 'array', 'max:25'],
            'links.*.type'         => ['required_with:links', 'string', Rule::in($types)],
            'links.*.value'        => ['required_with:links', 'string', 'max:255'],
            'links.*.label'        => ['nullable', 'string', 'max:80'],
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
