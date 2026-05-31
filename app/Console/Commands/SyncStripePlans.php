<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

/**
 * Creates (idempotently) a Stripe Product + recurring monthly Price for each
 * active plan and stores the resulting price id on the plan. Safe to re-run:
 * existing prices are matched by lookup_key.
 */
final class SyncStripePlans extends Command
{
    protected $signature = 'stripe:sync-plans {--force : Recreate prices even if the plan already has one}';

    protected $description = 'Sincroniza los planes locales con productos/precios de Stripe';

    public function handle(): int
    {
        $secret = (string) config('cashier.secret');
        if ($secret === '') {
            $this->error('STRIPE_SECRET no está configurado.');

            return self::FAILURE;
        }

        Stripe::setApiKey($secret);

        Plan::query()->where('is_active', true)->orderBy('sort_order')->get()->each(function (Plan $plan): void {
            if ($plan->stripe_price_id && ! $this->option('force')) {
                $this->line("• {$plan->name}: ya tiene price ({$plan->stripe_price_id}), omitido.");

                return;
            }

            $lookupKey = 'plan_'.$plan->slug;

            // Reuse an existing price with the same lookup_key when possible.
            $existing = Price::all(['lookup_keys' => [$lookupKey], 'active' => true, 'limit' => 1])->data[0] ?? null;

            if ($existing !== null && ! $this->option('force')) {
                $plan->forceFill(['stripe_price_id' => $existing->id])->save();
                $this->info("✓ {$plan->name}: reutilizado price {$existing->id}");

                return;
            }

            $product = Product::create([
                'name' => 'Dynamic Tattoos · '.$plan->name,
                'metadata' => ['plan_slug' => $plan->slug],
            ]);

            $interval = $plan->billing_cycle === 'yearly' ? 'year' : 'month';

            $priceParams = [
                'product' => $product->id,
                'unit_amount' => (int) round(((float) $plan->price) * 100),
                'currency' => 'eur',
                'recurring' => ['interval' => $interval],
                'lookup_key' => $lookupKey,
                'transfer_lookup_key' => true,
                'metadata' => ['plan_slug' => $plan->slug],
            ];

            if (config('billing.tax_enabled')) {
                // IVA incluido/excluido según config (necesario para Stripe Tax).
                $priceParams['tax_behavior'] = (string) config('billing.tax_behavior', 'inclusive');
            }

            $price = Price::create($priceParams);

            $plan->forceFill(['stripe_price_id' => $price->id])->save();
            $this->info("✓ {$plan->name}: creado price {$price->id} (€{$plan->price}/{$interval})");
        });

        $this->newLine();
        $this->info('Sincronización completada.');

        return self::SUCCESS;
    }
}
