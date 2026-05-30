<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TeamMember extends Model
{
    public const ROLES = ['owner', 'editor', 'viewer'];

    protected $fillable = [
        'owner_id',
        'member_user_id',
        'name',
        'email',
        'role',
        'status',
        'invitation_token',
        'invited_at',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }
}
