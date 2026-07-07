<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\StoragePack;
use Illuminate\Console\Command;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

/**
 * Crea (idempotente) un Product + Price de PAGO ÚNICO en Stripe para cada
 * paquete de ampliación de almacenamiento y guarda el price id en el paquete.
 * Espejo de SyncStripePlans, pero sin `recurring` (compra única, sin renovación).
 * Seguro de re-ejecutar: reutiliza precios existentes por lookup_key.
 */
final class SyncStripeStoragePacks extends Command
{
    protected $signature = 'stripe:sync-storage-packs {--force : Recrea los precios aunque el paquete ya tenga uno}';

    protected $description = 'Sincroniza los paquetes de almacenamiento con productos/precios (pago único) de Stripe';

    public function handle(): int
    {
        $secret = (string) config('cashier.secret');
        if ($secret === '') {
            $this->error('STRIPE_SECRET no está configurado.');

            return self::FAILURE;
        }

        Stripe::setApiKey($secret);

        StoragePack::query()->where('is_active', true)->orderBy('size_mb')->get()->each(function (StoragePack $pack): void {
            if ($pack->stripe_price_id && ! $this->option('force')) {
                $this->line("• {$pack->size_mb} MB: ya tiene price ({$pack->stripe_price_id}), omitido.");

                return;
            }

            $lookupKey = 'storage_pack_'.$pack->size_mb;

            // Reutiliza un precio existente con el mismo lookup_key cuando sea posible.
            $existing = Price::all(['lookup_keys' => [$lookupKey], 'active' => true, 'limit' => 1])->data[0] ?? null;

            if ($existing !== null && ! $this->option('force')) {
                $pack->forceFill(['stripe_price_id' => $existing->id])->save();
                $this->info("✓ {$pack->size_mb} MB: reutilizado price {$existing->id}");

                return;
            }

            $product = Product::create([
                'name' => 'Dynamic Tattoos · Ampliación '.$pack->size_mb.' MB',
                'metadata' => ['storage_pack_mb' => (string) $pack->size_mb],
            ]);

            // Pago único: SIN clave `recurring`.
            $priceParams = [
                'product' => $product->id,
                'unit_amount' => (int) round(((float) $pack->price) * 100),
                'currency' => 'eur',
                'lookup_key' => $lookupKey,
                'transfer_lookup_key' => true,
                'metadata' => ['storage_pack_mb' => (string) $pack->size_mb],
            ];

            if (config('billing.tax_enabled')) {
                $priceParams['tax_behavior'] = (string) config('billing.tax_behavior', 'inclusive');
            }

            $price = Price::create($priceParams);

            $pack->forceFill(['stripe_price_id' => $price->id])->save();
            $this->info("✓ {$pack->size_mb} MB: creado price {$price->id} (€{$pack->price} pago único)");
        });

        $this->newLine();
        $this->info('Sincronización de paquetes completada.');

        return self::SUCCESS;
    }
}
