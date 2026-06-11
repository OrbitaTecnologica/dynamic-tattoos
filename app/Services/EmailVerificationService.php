<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class EmailVerificationService
{
    /** Genera y envía un código nuevo. Devuelve false si está en cooldown. */
    public function issue(User $user): bool
    {
        if (! $this->canResend($user)) {
            return false;
        }

        // Invalida códigos previos sin consumir.
        EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $length = (int) config('verification.code_length');
        $max = (10 ** $length) - 1;
        $code = str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);

        EmailVerificationCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes((int) config('verification.ttl_minutes')),
            'attempts' => 0,
        ]);

        Mail::to($user->email)->send(new EmailVerificationCodeMail($code));

        return true;
    }

    /** Valida el código; en éxito marca el email como verificado. */
    public function verify(User $user, string $code): bool
    {
        $record = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if ($record === null || $record->expires_at->isPast()) {
            return false;
        }

        if ($record->attempts >= (int) config('verification.max_attempts')) {
            $record->forceFill(['consumed_at' => now()])->save();

            return false;
        }

        $record->increment('attempts');

        if (! hash_equals($record->code_hash, hash('sha256', $code))) {
            return false;
        }

        $record->forceFill(['consumed_at' => now()])->save();
        $user->markEmailAsVerified();

        return true;
    }

    /** True si no hay un código emitido dentro de la ventana de cooldown. */
    public function canResend(User $user): bool
    {
        $latest = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($latest === null) {
            return true;
        }

        $cooldown = (int) config('verification.resend_cooldown_seconds');

        return $latest->created_at->addSeconds($cooldown)->isPast();
    }
}
