<?php

namespace App\Models;

use App\Services\LinkCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkPageLink extends Model
{
    protected $fillable = [
        'link_page_id',
        'type',
        'label',
        'value',
        'custom_icon',
        'is_active',
        'position',
        'clicks_count',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'position'     => 'integer',
        'clicks_count' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(LinkPage::class, 'link_page_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkPageClick::class);
    }

    public function meta(): array
    {
        return LinkCatalog::type($this->type);
    }

    /** Normalizes `value` into a usable href depending on the type. */
    public function href(): string
    {
        return LinkCatalog::buildHref($this->type, (string) $this->value);
    }

    public function displayLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return $this->meta()['label'] ?? ucfirst($this->type);
    }
}
