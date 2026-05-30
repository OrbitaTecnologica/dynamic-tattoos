<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedInteger('storage_mb')->default(100)->after('max_tattoos');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedInteger('extra_storage_mb')->default(0)->after('currency');
        });

        Schema::create('storage_packs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('size_mb');
            $table->decimal('price', 8, 2);
            $table->string('label')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('bytes')->default(0);
            $table->string('type')->nullable(); // avatar | cover | gallery | ...
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('storage_packs');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('extra_storage_mb');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn('storage_mb');
        });
    }
};
