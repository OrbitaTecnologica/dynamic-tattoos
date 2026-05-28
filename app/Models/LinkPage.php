<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkPage extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'bio',
        'avatar_path',
        'cover_path',
        'theme_key',
        'theme_overrides',
        'onboarding_completed',
        'is_published',
        'views_count',
    ];

    protected $casts = [
        'theme_overrides'      => 'array',
        'onboarding_completed' => 'boolean',
        'is_published'         => 'boolean',
        'views_count'          => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(LinkPageLink::class)->orderBy('position');
    }

    public function publicUrl(): string
    {
        return route('link-page.show', ['slug' => $this->slug]);
    }
}
