<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Upload extends Model
{
    protected $fillable = [
        'user_id',
        'path',
        'disk',
        'bytes',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
