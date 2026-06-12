<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicAmbassadorController extends Controller
{
    /** Landing pública del embajador: registra la visita y muestra CTAs de registro. */
    public function show(string $slug, Request $request, ReferralService $referrals): View
    {
        /** @var User $ambassador */
        $ambassador = User::query()
            ->where('ambassador_slug', $slug)
            ->where('role', 'ambassador')
            ->firstOrFail();

        $code = $referrals->ensureCode($ambassador);
        $referrals->recordVisit($code, $request->ip(), $request->userAgent());

        return view('ambassador.landing', [
            'name' => $ambassador->name,
            'tierLabel' => $ambassador->tier?->label_es,
            'slug' => $slug,
            'referralCode' => $code,
            'frontendUrl' => rtrim((string) config('app.frontend_url', config('app.url')), '/'),
        ]);
    }
}
