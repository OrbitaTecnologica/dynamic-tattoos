<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TattooScanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tattoo_id' => $this->tattoo_id,
            'ip_address' => $this->ip_address,
            'country' => $this->country,
            'city' => $this->city,
            'device_type' => $this->device_type,
            'browser' => $this->browser,
            'user_agent' => $this->user_agent,
            'scanned_at' => $this->scanned_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
