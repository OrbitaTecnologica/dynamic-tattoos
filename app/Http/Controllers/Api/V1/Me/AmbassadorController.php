<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorTier;
use App\Models\CommissionWithdrawal;
use App\Models\Referral;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class AmbassadorController extends Controller
{
    /** Resumen para el dashboard del Embajador (tier actual, siguiente, ganancias, link). */
    public function summary(Request $request, ReferralService $referrals): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isAmbassador(), 403, 'Solo embajadores pueden acceder a este recurso.');

        $code = $referrals->ensureCode($user);

        $paidCount = $user->successfulReferralsCount();
        $pendingCount = $user->referralsMade()->where('status', Referral::STATUS_REGISTERED)->count();

        $tier = $user->tier;
        $nextTier = AmbassadorTier::query()
            ->where('sort_order', '>', $tier?->sort_order ?? -1)
            ->orderBy('sort_order')
            ->first();

        $linkUrl = $user->ambassador_slug !== null
            ? rtrim((string) config('app.url'), '/').'/e/'.$user->ambassador_slug
            : rtrim((string) config('app.frontend_url'), '/').'/register?ref='.$code;

        return response()->json([
            'data' => [
                'tier' => $tier ? [
                    'slug' => $tier->slug,
                    'label' => $tier->label_es,
                    'multiplier' => (float) $tier->commission_multiplier,
                ] : null,
                'next_tier' => $nextTier ? [
                    'slug' => $nextTier->slug,
                    'label' => $nextTier->label_es,
                    'multiplier' => (float) $nextTier->commission_multiplier,
                    'missing_refs' => max(0, (int) $nextTier->min_referrals - $paidCount),
                ] : null,
                'referrals' => [
                    'total_paid' => $paidCount,
                    'total_pending' => $pendingCount,
                ],
                'earnings_cents' => $user->referralEarningsCents(),
                'withdrawable_cents' => $user->withdrawableCents(),
                'payout_mode' => $user->plan?->payout_mode ?? 'credit',
                'can_withdraw' => $user->canWithdrawCommissions(),
                'withdrawal_min_cents' => (int) round(((float) config('billing.withdrawal_min_eur', 45.0)) * 100),
                'withdrawals' => $user->commissionWithdrawals()->latest()->get()->map(fn (CommissionWithdrawal $w): array => [
                    'id' => $w->id,
                    'amount_cents' => (int) $w->amount_cents,
                    'status' => $w->status,
                    'date' => $w->created_at?->toIso8601String(),
                ])->all(),
                'slug' => $user->ambassador_slug,
                'link_url' => $linkUrl,
                'referral_code' => $code,
            ],
        ]);
    }

    /** Cambio de slug compartible. Solo embajadores. Rate-limited a nivel de ruta. */
    public function updateSlug(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isAmbassador(), 403, 'Solo embajadores pueden cambiar el slug.');

        $reserved = config('ambassador.reserved_slugs', [
            'admin', 'api', 'app', 'login', 'logout', 'register', 'reset',
            'verify', 'me', 'cuenta', 'panel', 'embajador', 'embajadores',
            'tatuador', 'tatuadores', 'cliente', 'clientes', 'r', 'e', 't', 'u',
        ]);

        $validated = $request->validate([
            'slug' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:/^[a-z0-9-]+$/',
                Rule::notIn($reserved),
                Rule::unique('users', 'ambassador_slug')->ignore($user->id),
            ],
        ], [
            'slug.regex' => 'Solo letras minúsculas, números y guiones.',
            'slug.not_in' => 'Ese slug está reservado, elige otro.',
            'slug.unique' => 'Ese slug ya está en uso.',
        ]);

        $previous = $user->ambassador_slug;

        $user->forceFill(['ambassador_slug' => $validated['slug']])->save();

        activity('ambassador')
            ->causedBy($user)
            ->event('slug_updated')
            ->withProperties(['from' => $previous, 'to' => $validated['slug']])
            ->log('Slug embajador actualizado');

        return response()->json([
            'data' => [
                'slug' => $user->ambassador_slug,
                'link_url' => rtrim((string) config('app.url'), '/').'/e/'.$user->ambassador_slug,
            ],
        ]);
    }
}
