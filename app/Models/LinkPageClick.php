<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkPageClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'link_page_link_id',
        'ip',
        'referrer',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(LinkPageLink::class, 'link_page_link_id');
    }
}
