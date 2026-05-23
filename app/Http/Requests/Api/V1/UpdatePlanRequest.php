<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlanRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'billing_cycle' => ['sometimes', 'required', Rule::in(['monthly', 'yearly', 'lifetime'])],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'features' => ['sometimes', 'required', 'array'],
            'features.*' => ['string', 'max:255'],
            'max_tattoos' => ['sometimes', 'required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
