<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

final class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $online = $this->last_active_at !== null && $this->last_active_at->gt(now()->subMinutes(5));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'initials' => Str::upper(Str::substr($this->name ?? $this->email, 0, 2)),
            'role' => $this->role,
            'status' => $this->status,
            'online' => $online,
            'last_active_at' => $this->last_active_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
