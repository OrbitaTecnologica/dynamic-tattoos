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
            $table->enum('payout_mode', ['credit', 'cash'])
                ->default('credit')
                ->after('referral_reward');

            $table->json('allowed_roles')
                ->nullable()
                ->after('payout_mode');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['payout_mode', 'allowed_roles']);
        });
    }
};
