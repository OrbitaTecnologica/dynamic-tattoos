<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WithdrawRequest;
use App\Models\CommissionWithdrawal;
use App\Models\Referral;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

final class ReferralController extends Controller
{
    public function index(Request $request, ReferralService $referrals): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Referido universal: cualquier usuario autenticado tiene su enlace y ve sus
        // referidos/comisiones. El retiro en efectivo sigue restringido en withdraw().

        $code = $referrals->ensureCode($user);
        $shareUrl = rtrim((string) config('app.frontend_url'), '/').'/register?ref='.$code;

        $made = $user->referralsMade()->with('referred')->latest()->get();
        $paid = $made->where('status', Referral::STATUS_PAID);

        return response()->json([
            'data' => [
                'code' => $code,
                'share_url' => $shareUrl,
                'qr_svg' => (string) QrCodeGenerator::format('svg')->size(240)->margin(1)->generate($shareUrl),
                'reward_per_referral' => (float) ($user->plan?->referral_reward ?? config('billing.referral_reward', 0)),
                'can_withdraw' => $user->canWithdrawCommissions(),
                'withdrawable' => round($user->withdrawableCents() / 100, 2),
                'withdrawals' => $user->commissionWithdrawals()->latest()->get()->map(fn (CommissionWithdrawal $w): array => [
                    'id' => $w->id,
                    'amount' => round($w->amount_cents / 100, 2),
                    'status' => $w->status,
                    'date' => $w->created_at?->toIso8601String(),
                ])->all(),
                'stats' => [
                    'visits' => $user->referralVisits()->count(),
                    'registered' => $made->count(),
                    'paid' => $paid->count(),
                    'reward_total' => round($paid->sum('reward_cents') / 100, 2),
                ],
                'referrals' => $made->map(fn (Referral $r): array => [
                    'id' => $r->id,
                    'name' => $r->referred?->name,
                    'email' => $this->maskEmail($r->referred?->email),
                    'status' => $r->status,
                    'reward' => round($r->reward_cents / 100, 2),
                    'date' => $r->created_at?->toIso8601String(),
                ])->all(),
            ],
        ]);
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->hasReferralPlan(), 403, 'Tu plan no participa del programa de referidos.');
        abort_unless($user->canWithdrawCommissions(), 403, 'Tu plan no permite retirar comisiones en efectivo.');

        $amountCents = (int) round(((float) $request->validated()['amount']) * 100);

        $minCents = (int) round(((float) config('billing.withdrawal_min_eur', 45.0)) * 100);
        abort_if($amountCents < $minCents, 422, 'El retiro mínimo es de '.number_format($minCents / 100, 0, ',', '.').' €.');
        abort_if($amountCents > $user->withdrawableCents(), 422, 'El importe supera tu saldo disponible.');

        $withdrawal = $user->commissionWithdrawals()->create([
            'amount_cents' => $amountCents,
            'status' => CommissionWithdrawal::STATUS_REQUESTED,
        ]);

        return response()->json([
            'data' => [
                'id' => $withdrawal->id,
                'amount' => round($amountCents / 100, 2),
                'status' => $withdrawal->status,
                'withdrawable' => round($user->withdrawableCents() / 100, 2),
            ],
        ], 201);
    }

    private function maskEmail(?string $email): ?string
    {
        if ($email === null || ! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);

        return $visible.str_repeat('*', max(1, mb_strlen($local) - 2)).'@'.$domain;
    }
}
