<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CommissionWithdrawal extends Model
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'amount_cents',
        'status',
        'method',
        'notes',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
