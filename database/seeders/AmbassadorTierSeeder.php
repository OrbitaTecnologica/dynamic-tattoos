<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AmbassadorTier;
use Illuminate\Database\Seeder;

final class AmbassadorTierSeeder extends Seeder
{
    public function run(): void
    {
        // Tres niveles del programa Embajador. min_referrals = umbral de referidos
        // pagados para alcanzar el tier; commission_multiplier multiplica la
        // comisión base del plan del embajador (ver ReferralService::rewardOnPaid).
        // Valores iniciales; ajustables vía nuevo seed/migración si negocio cambia.
        $tiers = [
            [
                'slug' => 'bronze',
                'label_es' => 'Bronce',
                'min_referrals' => 0,
                'commission_multiplier' => 1.00,
                'sort_order' => 1,
            ],
            [
                'slug' => 'silver',
                'label_es' => 'Plata',
                'min_referrals' => 10,
                'commission_multiplier' => 1.25,
                'sort_order' => 2,
            ],
            [
                'slug' => 'gold',
                'label_es' => 'Oro',
                'min_referrals' => 50,
                'commission_multiplier' => 1.50,
                'sort_order' => 3,
            ],
        ];

        foreach ($tiers as $tier) {
            AmbassadorTier::query()->updateOrCreate(['slug' => $tier['slug']], $tier);
        }
    }
}
