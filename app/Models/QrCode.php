<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'url',
        'color',
        'dots_type',
        'corners_square_type',
        'corners_dot_type',
        'tattooed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tattooed_at' => 'datetime',
        ];
    }

    public function isTattooed(): bool
    {
        return $this->tattooed_at !== null;
    }

    /**
     * Genera un identificador corto y único para el QR: 6 caracteres en
     * MAYÚSCULAS. Se excluyen los caracteres ambiguos (0/O, 1/I) para que el
     * enlace del tatuaje sea legible. Es lo único que se codifica en el QR
     * (d-t.me/{code}), para mantener el patrón lo más simple posible.
     */
    public static function generateUniqueCode(int $length = 6): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while (self::query()->where('slug', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
