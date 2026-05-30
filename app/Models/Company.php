<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Company extends Model
{
    protected $fillable = [
        'user_id',
        'is_professional',
        'name',
        'category',
        'email',
        'website',
        'address',
        'tax_id',
        'vat',
    ];

    protected function casts(): array
    {
        return [
            'is_professional' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
