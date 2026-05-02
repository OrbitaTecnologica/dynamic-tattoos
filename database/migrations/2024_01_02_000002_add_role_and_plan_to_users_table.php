<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->enum('role', ['admin', 'artist', 'user'])->default('user')->after('password');
            $table->foreignId('plan_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->timestamp('plan_expires_at')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['role', 'plan_expires_at']);
        });
    }
};
