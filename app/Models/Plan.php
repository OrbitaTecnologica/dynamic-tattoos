<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'stripe_price_id',
        'features',
        'max_tattoos',
        'storage_mb',
        'is_active',
        'is_featured',
        'is_referral',
        'referral_reward',
        'payout_mode',
        'allowed_roles',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_referral' => 'boolean',
            'referral_reward' => 'decimal:2',
            'allowed_roles' => 'array',
            'max_tattoos' => 'integer',
            'storage_mb' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<self> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // -------------------------------------------------------------------------
    // Route binding
    // -------------------------------------------------------------------------

    /**
     * Permite resolver {plan} por id (numérico) o por slug. Así el panel de
     * cuenta puede seguir usando el id y el landing puede usar el slug.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->newQuery()
            ->when(
                is_numeric($value),
                static fn (Builder $query): Builder => $query->where('id', (int) $value),
                static fn (Builder $query): Builder => $query->where('slug', (string) $value),
            )
            ->first();
    }
}
