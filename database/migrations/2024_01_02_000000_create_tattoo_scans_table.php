<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tattoo_scans', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tattoo_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop', 'bot', 'unknown'])->default('unknown');
            $table->string('browser', 80)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->index(['tattoo_id', 'scanned_at'], 'scans_tattoo_time_idx');
            $table->index('scanned_at', 'scans_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tattoo_scans');
    }
};
