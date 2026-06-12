<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('ambassador_tier_id')
                ->nullable()
                ->after('referred_by')
                ->constrained('ambassador_tiers')
                ->nullOnDelete();

            $table->string('ambassador_slug', 50)
                ->nullable()
                ->unique()
                ->after('referral_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ambassador_tier_id');
            $table->dropColumn('ambassador_slug');
        });
    }
};
