<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LinkPageLinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'display_label' => $this->displayLabel(),
            'value' => $this->value,
            'href' => $this->href(),
            'custom_icon' => $this->custom_icon,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'clicks_count' => $this->clicks_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
