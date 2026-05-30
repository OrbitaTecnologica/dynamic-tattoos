<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Billable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'birthdate',
        'gender',
        'phones',
        'avatar_path',
        'language',
        'timezone',
        'currency',
        'extra_storage_mb',
        'notification_preferences',
        'email',
        'password',
        'role',
        'plan_id',
        'plan_expires_at',
        'is_premium',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'plan_expires_at'   => 'datetime',
            'birthdate'         => 'date',
            'phones'            => 'array',
            'notification_preferences' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'password'          => 'hashed',
            'is_premium'        => 'boolean',
        ];
    }

    /**
     * Default notification preferences (email/push per category).
     *
     * @var array<string, array<string, bool>>
     */
    public const NOTIFICATION_DEFAULTS = [
        'qr_created' => ['email' => true, 'push' => true],
        'qr_scanned' => ['email' => false, 'push' => true],
        'billing'    => ['email' => true, 'push' => false],
        'security'   => ['email' => true, 'push' => true],
        'product'    => ['email' => true, 'push' => false],
        'marketing'  => ['email' => false, 'push' => false],
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function tattoos(): HasMany
    {
        return $this->hasMany(Tattoo::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function linkPage(): HasOne
    {
        return $this->hasOne(LinkPage::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'owner_id');
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /**
     * Storage usage summary in megabytes.
     *
     * @return array{used_mb: float, total_mb: int, percent: int, used_bytes: int}
     */
    public function storageUsage(): array
    {
        $usedBytes = (int) $this->uploads()->sum('bytes');
        $usedMb = round($usedBytes / 1048576, 2);
        $baseMb = (int) ($this->plan?->storage_mb ?? 100);
        $totalMb = $baseMb + (int) ($this->extra_storage_mb ?? 0);
        $percent = $totalMb > 0 ? (int) min(100, round($usedMb / $totalMb * 100)) : 0;

        return [
            'used_bytes' => $usedBytes,
            'used_mb' => $usedMb,
            'total_mb' => $totalMb,
            'percent' => $percent,
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    public function hasActivePlan(): bool
    {
        return $this->subscribed('default');
    }

    public function canAddTattoo(): bool
    {
        if (! $this->hasActivePlan() || $this->plan === null) {
            return false;
        }

        return $this->tattoos()->count() < $this->plan->max_tattoos;
    }
}
