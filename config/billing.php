<?php

declare(strict_types=1);

return [
    /*
    | Activa el cálculo de impuestos de Stripe (Stripe Tax) y la recogida del
    | NIF/IVA en el checkout. Mantener en false hasta haber activado Stripe Tax
    | en el panel de Stripe; de lo contrario el checkout fallaría.
    */
    'tax_enabled' => (bool) env('STRIPE_TAX_ENABLED', false),

    /*
    | Cómo se interpretan los precios respecto al impuesto: 'inclusive' (IVA
    | incluido en el precio) o 'exclusive' (IVA añadido sobre el precio).
    */
    'tax_behavior' => env('STRIPE_TAX_BEHAVIOR', 'inclusive'),

    /*
    | Recompensa (en €) que recibe el suscriptor del plan Partner por cada
    | referido que se registra por su QR y paga una suscripción. Se acredita
    | como saldo en Stripe (se descuenta de su próximo cobro).
    */
    'referral_reward' => (float) env('REFERRAL_REWARD_EUR', 5.0),

    /*
    | Slugs de planes cuyas comisiones de referido son retirables en efectivo
    | (cash-out). Por defecto solo "empresa" (Negocio · retirable en dinero real).
    | Sobrescribir con WITHDRAWAL_PLANS="pro,empresa" si Pro también retira.
    */
    'withdrawal_plans' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('WITHDRAWAL_PLANS', 'empresa')),
    ))),
];
