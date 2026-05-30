<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('slug')->nullable()->after('user_id');
            $table->string('name')->nullable()->after('slug');

            $table->unique('slug');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'name']);
        });
    }
};
