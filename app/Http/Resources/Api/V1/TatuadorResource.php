<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TatuadorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'studio_name' => $this->studio_name,
            'artist_name' => $this->artist_name,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'maps_url' => $this->maps_url,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
        ];
    }
}
