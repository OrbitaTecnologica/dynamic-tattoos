<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QrCodeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'url' => $this->url,
            'color' => $this->color,
            'dots_type' => $this->dots_type,
            'corners_square_type' => $this->corners_square_type,
            'corners_dot_type' => $this->corners_dot_type,
            'tattooed' => $this->tattooed_at !== null,
            'tattooed_at' => $this->tattooed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
