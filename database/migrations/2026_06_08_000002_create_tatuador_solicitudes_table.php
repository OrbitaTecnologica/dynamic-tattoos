<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tatuador_solicitudes', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('studio_name', 150);
            $table->string('city', 120)->nullable();
            $table->string('email', 255);
            $table->string('phone', 40)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tatuador_solicitudes');
    }
};
