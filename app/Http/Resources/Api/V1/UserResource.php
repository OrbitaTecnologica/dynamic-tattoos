<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Domain\Plans\PlanFeatures;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $plan = $this->plan;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'birthdate' => $this->birthdate?->toDateString(),
            'gender' => $this->gender,
            'city' => $this->city,
            'country' => $this->country,
            'phones' => $this->phones ?? [],
            'avatar_url' => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'role' => $this->role,
            'is_ambassador' => $this->isAmbassador(),
            'is_artist' => $this->isArtist(),
            'is_admin' => $this->isAdmin(),
            'ambassador_slug' => $this->ambassador_slug,
            'ambassador_tier' => $this->tier ? [
                'slug' => $this->tier->slug,
                'label' => $this->tier->label_es,
                'multiplier' => (float) $this->tier->commission_multiplier,
            ] : null,
            'plan_id' => $this->plan_id,
            'plan' => $plan ? [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'allowed_content_types' => PlanFeatures::allowedContentTypes($plan->slug),
            ] : null,
            'is_premium' => $this->is_premium,
            'two_factor_enabled' => $this->two_factor_confirmed_at !== null,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
