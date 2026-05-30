<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNotificationsRequest extends FormRequest
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
            'notifications' => ['required', 'array'],
            'notifications.*.email' => ['required', 'boolean'],
            'notifications.*.push' => ['required', 'boolean'],
        ];
    }
}
