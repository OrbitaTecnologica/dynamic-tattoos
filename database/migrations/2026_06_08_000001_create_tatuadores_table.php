<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tatuadores', static function (Blueprint $table): void {
            $table->id();
            $table->string('studio_name', 150);
            $table->string('artist_name', 150)->nullable();
            $table->string('city', 120);
            $table->string('address', 255)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('maps_url', 1000)->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tatuadores');
    }
};
