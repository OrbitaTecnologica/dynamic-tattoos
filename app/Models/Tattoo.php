<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

final class Tattoo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'short_code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(static function (self $tattoo): void {
            if (empty($tattoo->short_code)) {
                $tattoo->short_code = self::generateUniqueShortCode();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Generates a URL-safe random string that is guaranteed to be unique
     * across the tattoos table. Retries until a collision-free code is found.
     */
    public static function generateUniqueShortCode(int $length = 8): string
    {
        do {
            $code = Str::random($length);
        } while (self::query()->where('short_code', $code)->exists());

        return $code;
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** All content rows ordered by their sort position. */
    public function contents(): HasMany
    {
        return $this->hasMany(TattooContent::class)->orderBy('order');
    }

    /** Convenience relation to retrieve the single active content row. */
    public function activeContent(): HasOne
    {
        return $this->hasOne(TattooContent::class)->where('is_active', true);
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
    public function scopeByShortCode(Builder $query, string $shortCode): Builder
    {
        return $query->where('short_code', $shortCode);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /** Returns the public URL encoded in the QR code. */
    public function getQrUrlAttribute(): string
    {
        return route('tattoo.show', ['shortCode' => $this->short_code]);
    }
}
