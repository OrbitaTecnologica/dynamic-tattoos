<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
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

        abort_unless($user->hasReferralPlan(), 403, 'La monitorización de referidos es exclusiva del plan Partner.');

        $code = $referrals->ensureCode($user);
        $shareUrl = rtrim((string) config('app.frontend_url'), '/').'/register?ref='.$code;

        $made = $user->referralsMade()->with('referred')->latest()->get();
        $paid = $made->where('status', Referral::STATUS_PAID);

        return response()->json([
            'data' => [
                'code' => $code,
                'share_url' => $shareUrl,
                'qr_svg' => (string) QrCodeGenerator::format('svg')->size(240)->margin(1)->generate($shareUrl),
                'reward_per_referral' => (float) config('billing.referral_reward', 0),
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
