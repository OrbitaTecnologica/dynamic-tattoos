<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Referral extends Model
{
    public const STATUS_REGISTERED = 'registered';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'status',
        'reward_cents',
        'credited_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_cents' => 'integer',
            'credited_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
