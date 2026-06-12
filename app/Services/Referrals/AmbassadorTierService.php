<?php

declare(strict_types=1);

namespace App\Services\Referrals;

use App\Models\AmbassadorTier;
use App\Models\User;

/**
 * Tiers del programa Embajador: Bronce/Plata/Oro según referidos pagados.
 * Solo aplica a usuarios con rol 'ambassador'. Solo promueve, nunca degrada.
 */
final class AmbassadorTierService
{
    /** Promueve al embajador al tier que le corresponda. Devuelve el tier vigente o null. */
    public function recompute(User $user): ?AmbassadorTier
    {
        if (! $user->isAmbassador()) {
            return null;
        }

        $count = $user->successfulReferralsCount();

        $newTier = AmbassadorTier::query()
            ->where('min_referrals', '<=', $count)
            ->orderByDesc('min_referrals')
            ->first();

        if ($newTier === null) {
            return null;
        }

        $currentSort = $user->tier?->sort_order ?? -1;

        if ($newTier->sort_order > $currentSort) {
            $user->forceFill(['ambassador_tier_id' => $newTier->id])->save();

            activity('ambassador')
                ->causedBy($user)
                ->event('tier_promoted')
                ->withProperties([
                    'from_sort' => $currentSort,
                    'to_tier' => $newTier->slug,
                    'detail' => 'Promovido a '.$newTier->label_es,
                ])
                ->log('Promovido a '.$newTier->label_es);
        }

        return $newTier;
    }

    /** Tier por defecto al registrarse como Embajador (el de menor min_referrals). */
    public function defaultTier(): AmbassadorTier
    {
        return AmbassadorTier::query()->orderBy('min_referrals')->firstOrFail();
    }
}
