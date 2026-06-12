<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassador_tiers', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label_es');
            $table->unsignedInteger('min_referrals');
            $table->decimal('commission_multiplier', 5, 2);
            $table->unsignedInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambassador_tiers');
    }
};
