<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TattooContent extends Model
{
    use HasFactory;

    // Typed constants require PHP 8.3; using untyped constants keeps compatibility
    // with PHP 8.2 as required by the project spec.
    public const TYPE_LINK    = 'link';
    public const TYPE_GALLERY = 'gallery';
    public const TYPE_VIDEO   = 'video';

    /** @var list<string> */
    public const TYPES = [self::TYPE_LINK, self::TYPE_GALLERY, self::TYPE_VIDEO];

    protected $fillable = [
        'tattoo_id',
        'type',
        'payload',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            // Laravel 11 automatically encodes/decodes JSON to associative array
            'payload'   => 'array',
            'is_active' => 'boolean',
            'order'     => 'integer',
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
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<self> $query */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Type helpers
    // -------------------------------------------------------------------------

    public function isLink(): bool
    {
        return $this->type === self::TYPE_LINK;
    }

    public function isGallery(): bool
    {
        return $this->type === self::TYPE_GALLERY;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * A Link content with a valid URL means the redirect controller can issue a
     * direct 302 to the external URL instead of rendering a Blade page.
     */
    public function isDirectRedirect(): bool
    {
        return $this->isLink() && ! empty($this->payload['url']);
    }
}
