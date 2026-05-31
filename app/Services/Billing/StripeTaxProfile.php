<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\User;
use Throwable;

/**
 * Empuja los datos fiscales del usuario (nombre, email y NIF/IVA) al Customer
 * de Stripe para que aparezcan en las facturas que emite Stripe. La dirección
 * de facturación se recoge en el propio Checkout (customer_update address auto).
 */
final class StripeTaxProfile
{
    public function sync(User $user): void
    {
        if (! config('billing.tax_enabled')) {
            return;
        }

        $user->createOrGetStripeCustomer();

        $company = $user->company;

        $user->updateStripeCustomer(array_filter([
            'name' => $company?->name ?: $user->name,
            'email' => $user->email,
        ]));

        $vat = $company?->vat ?: $company?->tax_id;

        if (is_string($vat) && $vat !== '') {
            $alreadyThere = collect($user->taxIds()->data ?? [])
                ->contains(fn ($taxId): bool => $taxId->value === $vat);

            if (! $alreadyThere) {
                try {
                    $user->createTaxId('eu_vat', $vat);
                } catch (Throwable) {
                    // Formato de IVA no válido para Stripe: se ignora y se podrá
                    // recoger en el checkout (tax_id_collection).
                }
            }
        }
    }
}
