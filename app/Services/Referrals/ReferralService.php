<?php

declare(strict_types=1);

namespace App\Services\Referrals;

use App\Models\Referral;
use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

/**
 * Programa de referidos del plan Partner: códigos, visitas del QR, vínculo en
 * el registro y recompensa (crédito en Stripe) cuando el referido paga.
 */
final class ReferralService
{
    /** Genera (si falta) y devuelve el código de referido del usuario. */
    public function ensureCode(User $user): string
    {
        if (is_string($user->referral_code) && $user->referral_code !== '') {
            return $user->referral_code;
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        $user->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    /** Registra una visita/escaneo del QR si el código pertenece a un referidor. */
    public function recordVisit(string $code, ?string $ip, ?string $userAgent): void
    {
        $referrerId = User::query()->where('referral_code', $code)->value('id');

        if ($referrerId === null) {
            return;
        }

        ReferralVisit::query()->create([
            'referrer_id' => $referrerId,
            'code' => $code,
            'ip' => $ip,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
            'created_at' => now(),
        ]);
    }

    /** Vincula al nuevo usuario con su referidor (en el registro). */
    public function attach(User $referred, ?string $code): void
    {
        if (! is_string($code) || $code === '') {
            return;
        }

        $referrer = User::query()->where('referral_code', $code)->first();

        if ($referrer === null || $referrer->id === $referred->id) {
            return;
        }

        $referred->forceFill(['referred_by' => $referrer->id])->save();

        Referral::query()->firstOrCreate(
            ['referred_id' => $referred->id],
            ['referrer_id' => $referrer->id, 'status' => Referral::STATUS_REGISTERED],
        );
    }

    /** El referido pagó: acredita la recompensa al referidor (una sola vez). */
    public function rewardOnPaid(User $referred): void
    {
        $referral = Referral::query()
            ->where('referred_id', $referred->id)
            ->where('status', Referral::STATUS_REGISTERED)
            ->first();

        if ($referral === null) {
            return;
        }

        $referrer = $referral->referrer;

        // Importe base: la recompensa propia del plan del referente si la define;
        // si no, el valor global de config('billing.referral_reward').
        $baseEuros = $referrer?->plan?->referral_reward ?? config('billing.referral_reward', 0);

        // Multiplicador por tier: solo aplica a embajadores. Tatuadores reciben
        // siempre la comisión base de su plan.
        $multiplier = ($referrer?->role === 'ambassador')
            ? (float) ($referrer?->tier?->commission_multiplier ?? 1.0)
            : 1.0;

        $rewardEuros = (float) $baseEuros * $multiplier;
        $rewardCents = (int) round($rewardEuros * 100);

        if ($referrer !== null && $rewardCents > 0) {
            try {
                $referrer->createOrGetStripeCustomer();
                $referrer->creditBalance($rewardCents, 'Recompensa por referido');

                activity('billing')
                    ->causedBy($referrer)
                    ->event('referral_reward')
                    ->withProperties(['detail' => 'Crédito por referido: '.number_format($rewardCents / 100, 2, ',', '.').' €'])
                    ->log('Recompensa por referido');
            } catch (Throwable) {
                // Si falla el crédito en Stripe, igualmente marcamos la conversión;
                // el crédito puede reconciliarse manualmente.
            }
        }

        $referral->forceFill([
            'status' => Referral::STATUS_PAID,
            'reward_cents' => $rewardCents,
            'credited_at' => now(),
        ])->save();

        // Si el referente es Embajador, puede que este pago lo promueva de tier.
        if ($referrer !== null && $referrer->isAmbassador()) {
            app(AmbassadorTierService::class)->recompute($referrer);
        }
    }
}
