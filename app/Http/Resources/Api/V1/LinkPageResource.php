<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class LinkPageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'theme_key' => $this->theme_key,
            'theme_overrides' => $this->theme_overrides,
            'is_published' => $this->is_published,
            'views_count' => $this->views_count,
            'public_url' => $this->publicUrl(),
            'links' => LinkPageLinkResource::collection($this->whenLoaded('links')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
