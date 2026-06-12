<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AmbassadorTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label_es',
        'min_referrals',
        'commission_multiplier',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_referrals' => 'integer',
            'commission_multiplier' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'ambassador_tier_id');
    }
}
