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
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->date('birthdate')->nullable()->after('last_name');
            $table->string('gender', 20)->nullable()->after('birthdate');
            $table->json('phones')->nullable()->after('gender');
            $table->string('avatar_path')->nullable()->after('phones');

            $table->string('language', 5)->default('es')->after('avatar_path');
            $table->string('timezone', 64)->default('Europe/Madrid')->after('language');
            $table->string('currency', 3)->default('EUR')->after('timezone');

            $table->json('notification_preferences')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name', 'last_name', 'birthdate', 'gender', 'phones', 'avatar_path',
                'language', 'timezone', 'currency', 'notification_preferences',
            ]);
        });
    }
};
