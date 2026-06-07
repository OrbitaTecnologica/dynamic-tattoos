<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_withdrawals', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->enum('status', ['requested', 'approved', 'paid', 'rejected'])->default('requested');
            $table->string('method', 60)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_withdrawals');
    }
};
