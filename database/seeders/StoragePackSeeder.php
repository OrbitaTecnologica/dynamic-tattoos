<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StoragePack;
use Illuminate\Database\Seeder;

final class StoragePackSeeder extends Seeder
{
    public function run(): void
    {
        $packs = [
            ['size_mb' => 100, 'price' => 0.99, 'label' => 'Básico', 'sort_order' => 1],
            ['size_mb' => 500, 'price' => 3.99, 'label' => 'Recomendado', 'sort_order' => 2],
            ['size_mb' => 1000, 'price' => 6.99, 'label' => 'Pro', 'sort_order' => 3],
        ];

        foreach ($packs as $pack) {
            StoragePack::query()->updateOrCreate(
                ['size_mb' => $pack['size_mb']],
                $pack + ['is_active' => true],
            );
        }
    }
}
