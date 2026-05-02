<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TattooScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tattoo_id',
        'ip_address',
        'country',
        'city',
        'device_type',
        'browser',
        'user_agent',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function tattoo(): BelongsTo
    {
        return $this->belongsTo(Tattoo::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder<self> $query */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('scanned_at', '>=', now()->subHours($hours));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Detects device type from a user-agent string.
     * Returns one of the enum values defined in the migration.
     */
    public static function detectDevice(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'bot') || str_contains($ua, 'crawl') || str_contains($ua, 'spider')) {
            return 'bot';
        }

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        if (str_contains($ua, 'mozilla') || str_contains($ua, 'chrome') || str_contains($ua, 'safari')) {
            return 'desktop';
        }

        return 'unknown';
    }

    /**
     * Extracts a simplified browser name from a user-agent string.
     */
    public static function detectBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'edg/')    => 'Edge',
            str_contains($ua, 'opr/')    => 'Opera',
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'chrome')  => 'Chrome',
            str_contains($ua, 'safari')  => 'Safari',
            default                      => 'Unknown',
        };
    }
}
