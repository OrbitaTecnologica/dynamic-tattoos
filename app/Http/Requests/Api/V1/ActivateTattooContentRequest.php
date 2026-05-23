<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\TattooContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ActivateTattooContentRequest extends FormRequest
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
            'type' => ['required', Rule::in(TattooContent::TYPES)],
            'payload' => ['required', 'array'],
            'payload.url' => ['required_if:type,link,video', 'nullable', 'url', 'max:2048'],
            'payload.label' => ['nullable', 'string', 'max:255'],
            'payload.open_in_new_tab' => ['nullable', 'boolean'],
            'payload.title' => ['nullable', 'string', 'max:255'],
            'payload.platform' => ['required_if:type,video', 'nullable', Rule::in(['youtube', 'vimeo', 'tiktok'])],
            'payload.autoplay' => ['nullable', 'boolean'],
            'payload.images' => ['required_if:type,gallery', 'nullable', 'array', 'min:1'],
            'payload.images.*' => ['string', 'max:512'],
        ];
    }
}
