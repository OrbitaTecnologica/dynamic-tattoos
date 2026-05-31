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
            // Recompensa por referido (en €) específica del plan. Si es null, se
            // usa el valor global de config('billing.referral_reward').
            $table->decimal('referral_reward', 8, 2)->nullable()->after('is_referral');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn('referral_reward');
        });
    }
};
