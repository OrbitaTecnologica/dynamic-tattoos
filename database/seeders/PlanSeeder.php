<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Modelo oficial v2: 5 niveles con comisiones de referido (€/alta que paga).
        // Precios anuales. La comisión vive en `referral_reward`; todos los planes
        // participan del programa de referidos (is_referral). Pro es el destacado.
        // Nota: max_tattoos y storage_mb son valores de producto a confirmar.
        $plans = [
            [
                'slug' => 'embajador',
                'name' => 'Embajador',
                'price' => 0,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 1,
                'storage_mb' => 0,
                'referral_reward' => 5.00,
                'payout_mode' => 'credit',
                'allowed_roles' => ['ambassador'],
                'is_referral' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'features' => [
                    'Panel de control intuitivo',
                    'Seguimiento de tus recomendaciones',
                    'Acumula ganancias y sube de nivel automáticamente',
                    'Gana 5 € por cada recomendación que se haga cliente',
                ],
            ],
            [
                'slug' => 'start',
                'name' => 'Start',
                'price' => 35,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 1,
                'storage_mb' => 300,
                'referral_reward' => 7.50,
                'payout_mode' => 'credit',
                'allowed_roles' => ['artist'],
                'is_referral' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'features' => [
                    'Todo lo del plan Embajador',
                    'Hasta 300 Mb en fotos Full HD',
                    'Redirección a la URL que quieras',
                    'Estadísticas básicas de escaneo',
                    'Gana 7,50 € por cada recomendación',
                ],
            ],
            [
                'slug' => 'plus',
                'name' => 'Plus',
                'price' => 55,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 1,
                'storage_mb' => 500,
                'referral_reward' => 10.00,
                'payout_mode' => 'credit',
                'allowed_roles' => ['artist'],
                'is_referral' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'features' => [
                    'Todo lo del plan Start',
                    'Hasta 500 Mb en videos Full HD',
                    'Redirección a la URL que quieras',
                    'Estadísticas básicas de escaneo',
                    'Gana 10,00 € por cada recomendación',
                ],
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'price' => 65,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 5,
                'storage_mb' => 1000,
                'referral_reward' => 12.50,
                'payout_mode' => 'credit',
                'allowed_roles' => ['artist'],
                'is_referral' => true,
                'is_featured' => true,
                'sort_order' => 4,
                'features' => [
                    'Todo lo del plan Plus',
                    'Hasta 1 Gb en archivos',
                    'Estadísticas avanzadas de escaneo',
                    'Enlaces tipo Linktree integrados',
                    'Opciones profesionales para tu presencia online',
                    'Gana 12,50 € por cada recomendación',
                ],
            ],
            [
                'slug' => 'empresa',
                'name' => 'Negocio',
                'price' => 100,
                'billing_cycle' => 'yearly',
                'max_tattoos' => 99,
                'storage_mb' => 5000,
                'referral_reward' => 15.00,
                'payout_mode' => 'cash',
                'allowed_roles' => ['ambassador'],
                'is_referral' => true,
                'is_featured' => false,
                'sort_order' => 5,
                'features' => [
                    'Todo lo del plan Pro',
                    'Primer año GRATIS (promoción limitada)',
                    'Gana 15 € por cliente, retirable en dinero real',
                    'Diseñado para escalar, vender y generar ingresos constantes',
                    'Ideal para creadores y profesionales que quieren destacar',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['is_active' => true],
            );
        }

        // Planes del modelo viejo que sigan en la BD: se desactivan (no se borran,
        // por si hay usuarios con plan_id apuntando a ellos) para que no aparezcan
        // en GET /plans.
        Plan::query()
            ->whereNotIn('slug', array_column($plans, 'slug'))
            ->update(['is_active' => false]);
    }
}
