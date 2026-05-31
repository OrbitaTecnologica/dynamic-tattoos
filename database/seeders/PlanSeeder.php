<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'basico',
                'name' => 'Básico',
                'price' => 4.90,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 1,
                'storage_mb' => 100,
                'sort_order' => 1,
                'features' => [
                    'QR único + ID permanente',
                    'Redirección ilimitada a URL externa',
                    'Imagen estática alojada (1 archivo)',
                    'Panel web de gestión',
                    'Estadísticas básicas de escaneo',
                ],
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'price' => 9.90,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 5,
                'storage_mb' => 500,
                'sort_order' => 2,
                'is_featured' => true,
                'features' => [
                    'Todo lo del plan Básico',
                    'Hasta 5 videos alojados (Full HD)',
                    'Reproductor sin marca',
                    'Programación de cambios por fecha',
                    'Soporte prioritario',
                ],
            ],
            [
                'slug' => 'premium-top',
                'name' => 'Premium Top',
                'price' => 19.90,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 99,
                'storage_mb' => 5000,
                'sort_order' => 3,
                'features' => [
                    'Todo lo del plan Premium',
                    'Página de perfil personalizada (feed)',
                    'Galería ilimitada · video + foto',
                    'Enlaces tipo Linktree integrados',
                    'Dominio propio opcional',
                    'Acceso anticipado a 3×3 cm',
                ],
            ],
            [
                'slug' => 'partner',
                'name' => 'Partner',
                'price' => 29.90,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 99,
                'storage_mb' => 10000,
                'sort_order' => 4,
                'is_referral' => true,
                'features' => [
                    'Todo lo del plan Premium Top',
                    'QR de referidos para tu tienda',
                    'Recompensa por cada alta que pague',
                    'Panel de monitorización de referidos',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['is_active' => true],
            );
        }
    }
}
