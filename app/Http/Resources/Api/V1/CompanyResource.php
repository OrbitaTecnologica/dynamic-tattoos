<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'is_professional' => (bool) $this->is_professional,
            'name' => $this->name,
            'category' => $this->category,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'tax_id' => $this->tax_id,
            'vat' => $this->vat,
        ];
    }
}
