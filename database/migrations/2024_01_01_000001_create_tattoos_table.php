<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tattoos', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // short_code: the segment that appears in the QR URL (/t/{short_code})
            // Length 12 fits comfortably inside a QR with ECC level 'H'
            $table->string('short_code', 12)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Composite index optimises the lookup done on every QR scan
            $table->index(['short_code', 'is_active'], 'tattoos_short_code_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tattoos');
    }
};
